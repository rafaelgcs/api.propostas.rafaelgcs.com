<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Proposal;
use App\Models\ProposalItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProposalController extends Controller
{
    /**
     * Show One Proposal by ID
     * 
     * @group Proposal
     * @urlParam client_id string requierd The ID of the client to search. Example: 1
     * @urlParam id string requierd The ID of the proposal to search. Example: 1
     */
    public function showOneProposal($client_id, $id)
    {
        $proposal = Proposal::select('*')
            ->where('id', '=', $id)
            ->where('client_id', '=', $client_id)
            ->first();

        $proposal_client = Client::select('*')
            ->where('id', '=', $client_id)
            ->first();

        $proposal_items = ProposalItem::select('*')
            ->where('proposal_id', '=', $proposal["id"])
            ->get();

        $proposal["client"] = $proposal_client;
        $proposal["items"] = $proposal_items;

        $return = ["success" => true, "proposal" => $proposal["id"], "data" => $proposal, "message" => "Encontramos apenas essa proposta com o ID desejado!"];
        return response()->json($return);
    }

    /**
     * Show Proposal(s) by title
     * 
     * @group Proposal
     * @urlParam title string required The title of to search. Example: João
     */
    public function showOneByTitle($title)
    {
        $titleToSearch = urldecode($title);
        $proposals = Proposal::select('*')
            ->where('title', 'LIKE', "%$titleToSearch%")
            ->get();

        foreach ($proposals as $key => $proposal) {
            $proposal_items = ProposalItem::select('*')
                ->where('proposal_id', '=', $proposal["id"])
                ->get();

            $proposal["items"] = $proposal_items;
        }


        if (count($proposals) > 1) {
            $return = ["success" => true, "data" => $proposals, "message" => "Foram encontradas essas propostas com o title desejado!"];
            return response()->json($return);
        }
        if (count($proposals) == 1) {
            $return = ["success" => true, "data" => $proposals, "message" => "Encontramos apenas essa proposta com o title desejado!"];
            return response()->json($return);
        }
        if (count($proposals) == 0) {
            $return = ["success" => false, "data" => $proposals, "message" => "Não foram encontradas propostas com o title desejado!"];
            return response()->json($return);
        }

        return response()->json();
    }

    /**
     * Show all count Proposals
     * 
     * @group Proposal
     */
    public function showAllCountProposals()
    {
        $proposals = Proposal::all();

        return response()->json(["success" => true, "count" => count($proposals)]);
    }
    /**
     * Show all Proposals
     * 
     * @group Proposal
     */
    public function showAllProposals()
    {
        $proposals = Proposal::all();

        if (count($proposals) != 0) {
            $return = ["success" => true, "data" => $proposals];
            return response()->json($return);
        } else {
            $return = ["success" => false, "data" => $proposals, "error" => ["message" => "Nenhum usuário foi encontrado!"]];
            return response()->json($return);
        }
    }

    /**
     * Show all Proposals by Client ID
     * 
     * @group Proposal
     * @urlParam client_id string requierd The ID of the client to search. Example: 1
     */
    public function showAllProposalsByClient($client_id)
    {
        $proposals = Proposal::select('*')
            ->where('client_id', '=', $client_id)
            ->get();

        if (count($proposals) != 0) {
            $return = ["success" => true, "data" => $proposals];
            return response()->json($return);
        } else {
            $return = ["success" => false, "data" => $proposals, "error" => ["message" => "Nenhum usuário foi encontrado!"]];
            return response()->json($return);
        }
    }

    /**
     * Show all Proposals by Client Name
     * 
     * @group Proposal
     * @urlParam client_name string requierd The name of the client to search. Example: 1
     */
    public function showAllProposalsByClientName($client_name)
    {
        $nameToSearch = urldecode($client_name);
        $clients = Client::select('id')
            ->where('name', 'LIKE', "%$nameToSearch%")
            ->get();

        $arrProposals = [];

        foreach ($clients as $key => $client) {
            $proposals = Proposal::select('*')
                ->where('client_id', '=', $client["id"])
                ->get()->toArray();

            $arrProposals = array_merge($arrProposals, $proposals);
        }

        return response()->json(["success" => true, "data" => $arrProposals]);
    }


    /**
     * Create Proposal
     * 
     * @group Proposal
     * @bodyParam title string required The title of the Proposal. Example: Proposta de Restyle de Marca
     * @bodyParam description string required The description of the Proposal. Example: Descrição breve do projeto
     * @bodyParam code string required The CODE of the proposal. Example: proposal_2021_restyle_marca
     * @bodyParam days_to_expires integer required The Days to expires the proposal. Example: 30
     * @bodyParam client_id integer required The ID of the Client. Example: 1
     * @bodyParam owner_id integer required The ID of the User Example: 1
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'code' => 'required|unique:proposals',
            'days_to_expires' => 'required',
            'client_id' => 'required',
            'owner_id' => 'required'
        ]);

        $proposal = $request->all();

        $nProposal = null;
        try {
            $nProposal = Proposal::create($proposal);
        } catch (Exception $e) {
            return response()->json(["success" => false, "message" => "Erro ao criar a proposta, tente novamente..."], 400);
        }


        return response()->json($nProposal, 201);
    }

    /**
     * Create Proposal Item
     * 
     * @group Proposal
     * @bodyParam proposal_id string required The ID of the Proposal. Example: 1
     * @bodyParam title string required The title of the proposal item. Example: Pagamento
     * @bodyParam description string required The description of the Proposal item. Example: Pagamento do projeto deve ser feito em PIX
     */
    public function createItem(Request $request)
    {
        $this->validate($request, [
            'proposal_id' => 'required',
            'title' => 'required',
            'description' => 'required'
        ]);

        $proposalItem = $request->all();

        $proposal = Proposal::select('*')
            ->where('id', '=', $proposalItem["proposal_id"])
            ->first();

        if ($proposal != null) {
            $nProposalItem = null;
            try {
                $proposalItem["description"] = json_encode($proposalItem["description"]);
                $nProposalItem = ProposalItem::create($proposalItem);
            } catch (Exception $e) {
                return response()->json(["success" => false, "message" => "Erro ao criar a proposta, tente novamente..."], 400);
            }
            return response()->json($nProposalItem, 201);
        } else {
            return response()->json(["success" => false, "message" => "Não existe a proposta indicada!"], 400);
        }
    }

    /**
     * Update Proposal
     * 
     * @group Proposal
     * @urlParam id string required The ID of the proposal to edit. Example: 1
     * @bodyParam title string required The title of the Proposal. Example: Proposta de Restyle de Marca
     * @bodyParam description string required The description of the Proposal. Example: Descrição breve do projeto
     * @bodyParam code string required The CODE of the proposal. Example: proposal_2021_restyle_marca
     * @bodyParam days_to_expires integer required The Days to expires the proposal. Example: 30
     * @bodyParam client_id integer required The ID of the Client. Example: 1
     * @bodyParam owner_id integer required The ID of the User Example: 1
     */
    public function update($id, Request $request)
    {
        $proposal = Proposal::select('*')
            ->where('id', '=', $id)
            ->first();
        $data = $request->all();

        $proposal->update($data);

        return response()->json(["success" => true, "proposal" => $proposal], 200);
    }

    /**
     * Delete Proposal
     * 
     * @group Proposal
     * @urlParam id string required The ID of the Proposal to delete.
     */
    public function delete($id)
    {
        Proposal::findOrFail($id)->delete();
        return response('Deleted Successfully', 200);
    }
}
