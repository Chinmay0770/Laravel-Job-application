<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    // show register/ create form
    public function create(){
        return view('users.register');
    }

    // create new users
    public function store(Request $request){
        $formfields = $request->validate([
            'name' => ['required','min:3'],
            'email' => ['required','email', Rule::unique('users','email')],
            'password' => 'required|confirmed|min:6'
        ]);

        // hash password
        $formfields['password'] = bcrypt($formfields['password']);

        $user = User::create($formfields);

        // auth
        auth()->login($user);

        return redirect('/')->with('message','User created and logged in.');
    }

    // logout
    public function logout(Request $request){
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('message','Logged Out');
    }

    // login 
    public function login(){
        return view('users.login');
    }

    // authenticate
    public function authenticate(Request $request){
        $formfields = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if(auth()->attempt($formfields)){
            $request->session()->regenerate();

            return redirect('/')->with('message','Logged In');
        }
        return back()->withErrors(['email' => 'Invalid Credentials'])->onlyInput('email');
    } 
}
