"""Live preview check; credentials stay in ignored .local files, never output."""
import base64
import json
import re
import sys
from http.cookies import SimpleCookie
from pathlib import Path
from urllib.error import HTTPError
from urllib.parse import urlencode, urlparse
from urllib.request import HTTPRedirectHandler, Request, build_opener


class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self, *args):
        return None


base = sys.argv[1].rstrip('/') if len(sys.argv) > 1 else 'http://127.0.0.1:8001'
target = urlparse(base)
assert (target.scheme == 'http' and target.hostname == '127.0.0.1') or (
    target.scheme == 'https' and target.hostname.endswith('.trycloudflare.com')
), 'Use the loopback gate or its HTTPS preview address only'
opener = build_opener(NoRedirect)
cookies = SimpleCookie()


def request(path, authorization=None, values=None, forwarded='https'):
    headers = {'X-Forwarded-Proto': forwarded}
    if authorization:
        headers['Authorization'] = authorization
    if cookies:
        headers['Cookie'] = '; '.join(f'{k}={v.value}' for k, v in cookies.items())
    data = urlencode(values).encode() if values is not None else None
    if data is not None:
        headers['Content-Type'] = 'application/x-www-form-urlencoded'
    try:
        response = opener.open(Request(base + path, data=data, headers=headers), timeout=20)
    except HTTPError as error:
        response = error
    for cookie in response.headers.get_all('Set-Cookie', []):
        cookies.load(cookie)
    return response.status, response.headers, response.read().decode(errors='replace')


status, headers, _ = request('/login')
assert status == 401, 'Anonymous visitors can reach the application login'
assert headers.get('WWW-Authenticate', '').startswith('Basic '), 'Browser password prompt missing'
assert 'no-store' in headers.get('Cache-Control', ''), 'Gate response may be cached'
for path in ('/', '/learn', '/up', '/.env', '/build/example.css'):
    assert request(path)[0] == 401, f'Anonymous path not gated: {path}'
assert request('/login', values={})[0] == 401, 'Anonymous POST bypasses gate'
wrong = 'Basic ' + base64.b64encode(b'preview:deliberately-wrong').decode()
assert request('/login', wrong)[0] == 401, 'Wrong password accepted'
settings = json.loads(Path('.local/preview-gate-settings.json').read_text(encoding='utf-8'))
authorization = 'Basic ' + base64.b64encode(
    f"{settings['username']}:{settings['password']}".encode()
).decode()
status, headers, html = request('/login', authorization)
assert status == 200 and 'Welcome back.' in html, 'Correct gate password did not reach ChemTrack'
assert 'secure' in ';'.join(headers.get_all('Set-Cookie', [])).lower(), 'App cookie not secure'
assert 'http://' + target.netloc + '/build/' not in html, 'Insecure asset URL'
assets = {urlparse(u).path for u in re.findall(r'(?:src|href)="(https://[^\"]+/build/[^\"]+)"', html)}
assert assets, 'Compiled assets missing'
for asset in assets:
    assert request(asset)[0] == 401, 'Static asset bypasses gate'
    assert request(asset, authorization)[0] == 200, 'Authenticated static asset failed'
assert request('/learn', authorization)[0] == 302, 'Outer gate bypasses ChemTrack login'
token = re.search(r'name="_token"[^>]*value="([^"]+)"', html).group(1)
account = json.loads(Path('.local/demo-accounts.json').read_text(encoding='utf-8'))[1]
status, headers, _ = request('/login', authorization, {
    '_token': token, 'email': account['email'], 'password': account['password'],
})
assert status == 302 and headers['Location'].endswith('/learn'), 'ChemTrack login POST failed behind gate'
for _ in range(3):
    status, _, html = request('/learn', authorization)
    assert status == 200 and 'Overall completion' in html, 'Dashboard refresh lost access'
assert 'href="/learn"' in html, 'Student logo destination missing'
status, _, home = request('/', authorization)
assert status == 200 and 'data-landing-menu' in home, 'Logo navigation lost access'
token = re.search(r'name="_token"[^>]*value="([^"]+)"', html).group(1)
assert request('/logout', authorization, {'_token': token})[0] == 302, 'App logout failed'
assert request('/learn', authorization)[0] == 302, 'App logout did not protect dashboard'
assert request('/.env', authorization)[0] == 404, 'Private file exposed'
assert request('/.local/preview-gate-settings.json', authorization)[0] == 404, 'Preview settings exposed'
if target.hostname == '127.0.0.1':
    status, headers, _ = request('/login', forwarded='http')
    assert status == 308 and headers['Location'].startswith('https://'), 'Insecure external request not redirected'
print('PASS: gate denies anonymous/wrong-password requests and assets; app login, refresh/logo, logout and private-file checks pass.')
