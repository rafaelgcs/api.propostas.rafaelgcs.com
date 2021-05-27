<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Show One User by ID
     * 
     * @group User
     * @urlParam id string requierd The ID of the user to search. Example: 1
     */
    public function showOneUser($id)
    {
        $users = User::select('*')
            ->where('id', '=', $id)
            ->get();

        if (count($users) > 1) {
            $return = ["success" => true, "data" => $users, "message" => "Foram encontrados esses usuários com o ID desejado!"];
            return response()->json($return);
        }
        if (count($users) == 1) {
            $return = ["success" => true, "data" => $users, "message" => "Encontramos apenas esse usuário com o ID desejado!"];
            return response()->json($return);
        }
        if (count($users) == 0) {
            $return = ["success" => false, "data" => $users, "message" => "Não foram encontrados usuários com o ID desejado!"];
            return response()->json($return);
        }
    }

    /**
     * Show User(s) by name
     * 
     * @group User
     * @urlParam name string required The name of to search. Example: João
     */
    public function showOneByName($name)
    {
        $nameToSearch = urldecode($name);
        $users = User::select('*')
            ->where('name', 'LIKE', "%$nameToSearch%")
            ->get();


        if (count($users) > 1) {
            $return = ["success" => true, "data" => $users, "message" => "Foram encontrados esses usuários com o nome desejado!"];
            return response()->json($return);
        }
        if (count($users) == 1) {
            $return = ["success" => true, "data" => $users, "message" => "Encontramos apenas esse usuário com o nome desejado!"];
            return response()->json($return);
        }
        if (count($users) == 0) {
            $return = ["success" => false, "data" => $users, "message" => "Não foram encontrados usuários com o nome desejado!"];
            return response()->json($return);
        }

        return response()->json();
    }

    /**
     * Show all count Users
     * 
     * @group User
     */
    public function showAllCountUsers()
    {
        $users = User::all();

        return response()->json(["success" => true, "count" => count($users)]);
    }
    /**
     * Show all Users
     * 
     * @group User
     */
    public function showAllUsers()
    {
        $users = User::all();

        if (count($users) != 0) {
            $return = ["success" => true, "data" => $users];
            return response()->json($return);
        } else {
            $return = ["success" => false, "data" => $users, "error" => ["message" => "Nenhum usuário foi encontrado!"]];
            return response()->json($return);
        }
    }


    /**
     * Create User
     * 
     * @group User
     * @bodyParam name string required The name of the User. Example: João da Silva
     * @bodyParam email string required The e-mail of the Admin. Example: teste@teste.com
     * @bodyParam username string required The username of the User. Example: joaosilva
     * @bodyParam rank string required The Rank of the User. Example: 1
     * @bodyParam password string required The password of the User. Example: &hj1931&%
     * @bodyParam password_confirmation string required The confirmation of the password for validate the User. Example: &hj1931&%
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'username' => 'required',
            'rank' => 'required',
            'password' => 'required|confirmed'
        ]);

        $user = $request->all();

        $user['password'] = Hash::make($user['password']); //base64_encode($user['password']);

        $nUser = null;
        try {
            $nUser = User::create($user);
        } catch (Exception $e) {
        }


        return response()->json($nUser, 201);
    }

    /**
     * Update User
     * 
     * @group User
     * @urlParam id string required The ID of the user to edit. Example: 1
     * @bodyParam name string The name of the User. Example: João da Silva
     * @bodyParam email string The e-mail of the Admin. Example: teste@teste.com
     * @bodyParam username string The username of the User. Example: joaosilva
     * @bodyParam rank string The Rank of the User. Example: 1
     * @bodyParam password string The password of the User. Example: &hj1931&%
     */
    public function update($id, Request $request)
    {
        $user = User::select('*')
            ->where('id', '=', $id)
            ->first();
        $data = $request->all();

        if (!empty($data['password'])) {
            $data['last_password'] = $user->password;
            $data['password'] = Hash::make($data['password']); //base64_encode($data['password']);
        }
        $user->update($data);

        return response()->json(["success" => true, "user" => $user], 200);
    }

    /**
     * Update User
     * 
     * @group User
     * @urlParam id string required The ID of the user to edit. Example: 1
     * @bodyParam cod required string The COD of the User Password Request. Example: &1231sadasdas1&%
     * @bodyParam password required string The password of the User. Example: &hj1931&%
     * @bodyParam password_confirmation required string The password of the User. Example: &hj1931&%
     */
    public function updatePassword($id, Request $request)
    {
        $this->validate($request, ["cod" => 'required', "password" => 'required|confirmed']);
        $data = $request->all();
        if (base64_decode($data['cod']) == $id) {
            $user = User::select('*')
                ->where('id', '=', $id)
                ->first();
            $update = ["password" => Hash::make($data['password']), "last_password" => $user->password];

            $user->update($update);

            return response()->json(["success" => true], 200);
        } else {
            return response()->json(["success" => false, "cod" => base64_decode($data['cod']), "id" => $id], 200);
        }
    }

    /**
     * Delete User
     * 
     * @group User
     * @urlParam id string required The ID of the User to delete.
     */
    public function delete($id)
    {
        User::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }
}
