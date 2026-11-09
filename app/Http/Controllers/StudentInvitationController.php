<?php

namespace App\Http\Controllers;

use App\Models\Programme;
use App\Services\StudentAccounts;
use Illuminate\Http\Request;

class StudentInvitationController extends Controller
{
    public function create(Request $request)
    {
        abort_unless($request->user()->role === 'teacher', 403);

        return response()->view('teacher.invitation-create', ['programmes' => Programme::where('teacher_id', $request->user()->id)->orderBy('title')->get()])->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $request, StudentAccounts $accounts)
    {
        abort_unless($request->user()->role === 'teacher', 403);
        $data = $request->validate(['programme_id' => 'required|integer']);
        $token = $accounts->invite($request->user(), (int) $data['programme_id']);

        return redirect('/teacher/student-invitations/created')->with('student_invitation_url', url('/register').'?'.http_build_query(['invitation' => $token]));
    }

    public function created(Request $request)
    {
        abort_unless($request->user()->role === 'teacher', 403);
        $url = $request->session()->pull('student_invitation_url');
        if (! $url) {
            return redirect('/teacher/student-invitations');
        }

        return response()->view('teacher.invitation-created', compact('url'))->header('Cache-Control', 'no-store, private')->header('Referrer-Policy', 'no-referrer');
    }
}
