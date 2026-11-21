<?php

namespace App\Http\Controllers;

use App\Services\ProgrammeInvitations;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JoinProgrammeController extends Controller
{
    public const PENDING_CODE = 'join.code';

    public function show(Request $request, ProgrammeInvitations $invitations)
    {
        $code = $invitations->normalize((string) ($request->query('code') ?? $request->session()->get(self::PENDING_CODE, '')));
        $user = $request->user();
        if (! $user) {
            if ($code !== '') {
                // Held in the session so the code survives signup or sign-in.
                $request->session()->put(self::PENDING_CODE, $code);
            }

            return redirect('/register')->with('status', 'Create your student account first, then you will join your teacher\'s programme. Already have an account? Sign in instead.');
        }
        if ($user->role !== 'student') {
            return redirect('/teacher');
        }
        $invitation = null;
        $problem = null;
        if ($code !== '') {
            try {
                $invitation = $invitations->available($code);
            } catch (ValidationException $exception) {
                $problem = $exception->errors()['code'][0];
            }
        }

        return response()->view('learning.join', compact('code', 'invitation', 'problem'))->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $request, ProgrammeInvitations $invitations)
    {
        abort_unless($request->user()->role === 'student', 403);
        $data = $request->validate(['code' => 'required|string|max:40']);
        $result = $invitations->join($request->user(), $data['code']);
        $request->session()->forget(self::PENDING_CODE);
        $title = $result['programme']->title;

        return redirect('/learn')->with('status', $result['joined'] ? 'You joined '.$title.'.' : 'You are already in '.$title.'.');
    }
}
