<?php

namespace App\Http\Controllers;

use App\Repository\UserRepository;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'email' => ['required','email','unique:users'],
            'password' => ['required','min:8','confirmed']
        ]);

        $this->userRepository->create($request->name, $request->email, $request->password);
    }
}
