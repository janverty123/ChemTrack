# Open ChemTrack on this computer

Updated: 3 November 2026 (Asia/Manila).

## Open it now

Use [ChemTrack](http://127.0.0.1:8002/) or [teacher workspace](http://127.0.0.1:8002/teacher). The local preview uses port 8002 and a separate session cookie; no outer password is required. Keep the laptop and local database/server running. The remote preview is stopped, so old temporary HTTPS links do not work.

Existing synthetic teacher credentials are in ignored `.local/demo-accounts.json`; the coded student account is in ignored `.local/site-update/updated-preview.json`. Never publish these files. Account and learning history have been preserved. See [presentation review](presentation-polish.md) for current features and checks.

The ordinary launcher below uses port 8000 after a restart. Use whichever address its open window actually reports.

## Reopen after a restart or closed server

1. Open **XAMPP Control Panel** from the Start menu, or run `C:\xampp\xampp-control.exe`.
2. Check **MySQL**. If it is already running, leave it running. Otherwise click its **Start** button. Apache is not required for this preview because the launcher starts PHP's development server.
3. Open File Explorer and go to your ChemTrack project folder.
4. Double-click **Start-ChemTrack.cmd**. Keep its window open. Wait until it says the server is running at `http://127.0.0.1:8000`.
5. Open `http://127.0.0.1:8000/login` in Chrome or Edge. Use the exact `http://` address.
6. Open `.local\demo-accounts.json` in Notepad for the local demo sign-in details, then sign in.

If the preview already opens, do not start another server. Closing the launcher window or restarting Windows stops this preview; running it again does not reset the stored student work. There is no need to reinstall dependencies, rebuild assets or rerun demo creation for an ordinary restart.

## If it still will not open

| Symptom | What to check |
| --- | --- |
| Browser says connection refused / cannot reach site | Check the launcher window is open and actually reports the server address. On 29 October the database was running but no website server was listening on port 8000; restarting the preview restored access |
| Launcher reports port 8000 is occupied | Try the existing login URL first. Do not launch repeated copies or stop unrelated services; keep the error text for investigation |
| Login page opens but signing in gives a database error | Check MySQL is running. Do not reset/delete the database or edit credentials to guess a fix |
| Launcher closes or shows an error | Its final pause should retain the message. Copy the error text for diagnosis; do not share `.env` or the demo password file |
| It fails on a phone or another computer | `127.0.0.1` points to the device opening it. This preview runs on this Windows computer only; remote student access requires the later hosting/deployment stage |

## Optional PowerShell start

From the project folder:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\dev.ps1 serve
```

This applies the execution-policy choice to that process only. The double-click launcher is the simpler route. See [development setup](README.md) for test/build commands and setup on another machine.
