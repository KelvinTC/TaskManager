<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function updateTheme(Request $request)
    {
        $request->validate([
            'dark_mode' => 'required|in:light,dark',
        ]);

        $user = Auth::user();
        $user->update([
            'dark_mode' => $request->dark_mode,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Theme preference updated successfully',
        ]);
    }
}
