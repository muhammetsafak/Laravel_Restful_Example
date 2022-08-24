<?php

namespace App\Http\Controllers;

use App\Classes\JWT;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $username = \trim($request->json('username', ''));
        $password = \trim($request->json('password', ''));
        if(empty($username) || empty($password)){
            return response()->json([
                'status'    => 0,
                'message'   => 'Login information is missing or incorrect.',
            ], 400);
        }


        if(\filter_var($username, \FILTER_VALIDATE_EMAIL)){
            $user = User::where('email', $username);
        }else{
            $user = User::where('username', $username);
        }
        $user = $user->first();
        if(!\password_verify($password, $user->password)){
            return response()->json([
                'status'    => 0,
                'message'   => 'Login information is missing or incorrect.',
            ], 400);
        }
        $data = [
            'id'        => $user->id,
            'name'      => $user->name,
            'surname'   => $user->surname,
            'username'  => $user->username,
            'email'     => $user->email,
        ];

        $token = JWT::encode($data);

        return response()->json([
            'status'    => 1,
            'message'   => 'Your ' . $user->username . ' login was successful.',
            'token'     => $token,
        ], 200);
    }

    public function register(Request $request)
    {
        $name = $request->json('name', '');
        $surname = $request->json('surname', '');
        $username = $request->json('username', '');
        $password = \trim($request->json('password', ''));
        $email = $request->json('email', '');

        if(empty($name) || empty($surname) || empty($username) || empty($password) || empty($email)){
            return response()->json([
                'status'    => 0,
                'message'   => 'Name, surname, username, password, email must be defined for registration.',
            ], 400);
        }

        if(!\filter_var($email, \FILTER_VALIDATE_EMAIL)){
            return response()->json([
                'status'    => 0,
                'message'   => 'Email must be an email address.',
            ], 400);
        }
        $user = User::orWhere('email', $email)->orWhere('username', $username)->first();
        if(!empty($user)){
            return response()->json([
                'status'    => 0,
                'message'   => 'Username or email is being used by another account.',
            ], 400);
        }

        $data = [
            'name'      => $name,
            'surname'   => $surname,
            'username'  => $username,
            'email'     => $email,
            'password'  => \password_hash($password, \PASSWORD_DEFAULT),
        ];
        $user = User::create($data);
        unset($data['password']);
        $data['id'] = $user->id;

        $token = JWT::encode($data);

        return response()->json([
            'status'    => 1,
            'message'   => 'The user has been created.',
            'token'     => $token,
        ], 201);
    }

    public function destroy()
    {
        $user = JWT::get();
        if(empty($user)){
            return response()->json([
                'status'    => 0,
                'message'   => 'Your identity could not be verified.',
            ], 401);
        }
        $count = Order::where('status', '!=', 'delivered')->count();
        if($count > 0){
            return response()->json([
                'status'    => 0,
                'message'   => 'You have unfinished orders yet.',
            ], 400);
        }
        $delete = User::destroy([$user['id']]);
        if($delete !== 0){
            Order::where('user_id', $user['id'])->delete();
        }
        return response()->json([
            'status'    => ($delete === 0 ? 0 : 1),
        ], 200);
    }


}
