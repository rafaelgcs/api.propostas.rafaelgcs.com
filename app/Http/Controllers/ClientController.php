<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    /**
     * Show One Client by ID
     * 
     * @group Client
     * @urlParam id string requierd The ID of the user to search. Example: 1
     */
    public function showOneClient($id)
    {
        $clients = Client::select('*')
            ->where('id', '=', $id)
            ->get();

        if (count($clients) > 1) {
            $return = ["success" => true, "data" => $clients, "message" => "Foram encontrados esses clientes com o ID desejado!"];
            return response()->json($return);
        }
        if (count($clients) == 1) {
            $return = ["success" => true, "data" => $clients, "message" => "Encontramos apenas esse cliente com o ID desejado!"];
            return response()->json($return);
        }
        if (count($clients) == 0) {
            $return = ["success" => false, "data" => $clients, "message" => "Não foram encontrados clientes com o ID desejado!"];
            return response()->json($return);
        }
    }

    /**
     * Show Client(s) by name
     * 
     * @group Client
     * @urlParam name string required The name of to search. Example: João
     */
    public function showOneByName($name)
    {
        $nameToSearch = urldecode($name);
        $clients = Client::select('*')
            ->where('name', 'LIKE', "%$nameToSearch%")
            ->get();


        if (count($clients) > 1) {
            $return = ["success" => true, "data" => $clients, "message" => "Foram encontrados esses clientes com o nome desejado!"];
            return response()->json($return);
        }
        if (count($clients) == 1) {
            $return = ["success" => true, "data" => $clients, "message" => "Encontramos apenas esse cliente com o nome desejado!"];
            return response()->json($return);
        }
        if (count($clients) == 0) {
            $return = ["success" => false, "data" => $clients, "message" => "Não foram encontrados clientes com o nome desejado!"];
            return response()->json($return);
        }

        return response()->json();
    }

    /**
     * Show all count Clients
     * 
     * @group Client
     */
    public function showAllCountClients()
    {
        $clients = Client::all();

        return response()->json(["success" => true, "count" => count($clients)]);
    }
    /**
     * Show all Clients
     * 
     * @group Client
     */
    public function showAllClients()
    {
        $clients = Client::all();

        if (count($clients) != 0) {
            $return = ["success" => true, "data" => $clients];
            return response()->json($return);
        } else {
            $return = ["success" => false, "data" => $clients, "error" => ["message" => "Nenhum usuário foi encontrado!"]];
            return response()->json($return);
        }
    }


    /**
     * Create Client
     * 
     * @group Client
     * @bodyParam name string required The name of the Client. Example: João da Silva
     * @bodyParam email string required The e-mail of the client. Example: teste@teste.com
     * @bodyParam owner_id string required The ID of the User. Example: 1
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'owner_id' => 'required',
        ]);

        $client = $request->all();
        $nClient = null;
        try {
            $nClient = Client::create($client);
        } catch (Exception $e) {
        }


        return response()->json($nClient, 201);
    }

    /**
     * Update Client
     * 
     * @group Client
     * @urlParam id integer required The ID of the client to Edit. Example: 1
     * @bodyParam name string The name of the Client. Example: João da Silva
     * @bodyParam email string The e-mail of the client. Example: teste@teste.com
     * @bodyParam owner_id string The ID of the User. Example: 1
     */
    public function update($id, Request $request)
    {
        $client = Client::select('*')
            ->where('id', '=', $id)
            ->first();
        $data = $request->all();

        $client->update($data);

        return response()->json(["success" => true, "client" => $client], 200);
    }

    /**
     * Delete Client
     * 
     * @group Client
     * @urlParam id string required The ID of the Client to delete.
     */
    public function delete($id)
    {
        Client::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }
}
