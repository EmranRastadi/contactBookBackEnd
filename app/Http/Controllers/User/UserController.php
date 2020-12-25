<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Contacts;
use Validator;
use Illuminate\Http\Request;

use Illuminate\Routing\UrlGenerator;

class UserController extends Controller
{
    //
    protected $contacts;
    protected $baseUrl;

    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->middleware('auth:users');
        $this->contacts = new Contacts;
        $this->baseUrl = $urlGenerator->to("/");
    }

    public function addContacts(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'token' => 'required|string',
            'firstname' => 'required|string',
            'phonenumber' => 'required|string'
        ]);
        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validate->messages()->toArray()
            ], 400);
        }
        $picture = $request->picture;
        $file_name = '';
        if ($picture == null) {
            $file_name = 'avatar.png';
        } else {
            $generate_name = uniqid() . "_" . time() . date("Ymd") . "_IMG";
            $base64Image = $picture;
            $fileBin = file_get_contents($base64Image);
            $file_mim_type = $picture->getMimeType();
            if ($file_mim_type == "image/jpeg") {
                $file_name = $generate_name . ".jpeg";
            } else if ($file_mim_type == "image/png") {
                $file_name = $generate_name . ".png";
            } else if ($file_mim_type == "image/jpg") {
                $file_name = $generate_name . ".jpg";
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'image type should be PNG or JPEG or JPG'
                ], 401);
            }
        }

        $token = $request->token;
        $auth = auth("users")->authenticate($token);
        $user_id = $auth->id;

        $this->contacts->firstname = $request->firstname;
        $this->contacts->lastname = $request->lastname;
        $this->contacts->profile_image = $file_name;
        $this->contacts->email = $request->email;
        $this->contacts->phonenumber = $request->phonenumber;
        $this->contacts->userId = $user_id;

        $this->contacts->save();

        if ($picture == null) {

        } else {
            file_put_contents('./profile_img/' . $file_name, $fileBin);
        }

        return response()->json([
            'success' => true,
            'message' => 'data is saved successfully!'
        ], 200);

    }

    public function dataPagintae($token, $pagination = null)
    {
        $userid = auth('users')->authenticate($token)->id;
        $file_dir = $this->baseUrl . "/profile_img";
        if ($pagination == null || $pagination == '') {
            $data = $this->contacts->where('userId', $userid)->orderBy('id', 'DESC')->get()->toArray();
            return response()->json([
                'success' => true,
                'data' => $data,
                'file_dir' => $file_dir
            ], 200);
        }

        $paginate_data = $this->contacts->where("userId", $userid)->orderBy('id', 'DESC')->paginate($pagination);
        return response()->json([
            'success' => true,
            'data' => $paginate_data,
            'file_dir' => $file_dir
        ], 200);

    }

    public function editSingleData(Request $request, $id)
    {
        $validate = Validator::make($request->all(),
            [
                'firstname' => 'required|string',
                'phonenumber' => 'required|string'
            ]);
        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validate->messages()->toArray()
            ], 400);
        }
        $findData = $this->contacts::find($id);
        if (!$findData) {
            return response()->json([
                'success' => false,
                'message' => 'this contacts is not valid!'
            ], 400);
        }
        $getFile = $findData->profile_image;
        $getFile == "avatar.png" ?: unlink("./profile_img/" . $getFile);

        $profile_pric = $request->profile_image;
        $file_name = '';
        if (!$profile_pric) {
            $file_name = 'avatar.png';
        } else {
            $fileBin = file_get_contents($profile_pric);
            $file_mem_type = $profile_pric->getMimeType();
            $geneate_name = uniqid() . "_" . time() . date("Ymd") . "_IMG";
            if ($file_mem_type == "image/png") {
                $file_name = $geneate_name . ".png";
            } else if ($file_mem_type == "image/jpeg") {
                $file_name = $geneate_name . ".jpeg";
            } else if ($file_mem_type == "image/jpg") {
                $file_name = $geneate_name . ".jpg";
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'pro image should be have PNG or JPEG or JPG type'
                ], 401);
            }
        }

        $findData->firstname = $request->firstname;
        $findData->lastname = $request->lastname;
        $findData->phonenumber = $request->phonenumber;
        $findData->email = $request->email;
        $findData->profile_image = $file_name;
        $findData->save();
        if ($profile_pric == null) {

        } else {
            file_put_contents('./profile_img/' . $file_name, $fileBin);
        }

        return response()->json([
            'success' => true,
            'message' => 'update successfully !'
        ], 200);
    }

    public function deleteContacts($id)
    {
        $findData = $this->contacts::find($id);
        if (!$findData) {
            return response()->json([
                'success' => false,
                'message' => 'not found this contacs!'
            ], 400);
        }

        $getFile = $findData->profile_image;
        if ($findData->delete()) {
            $getFile == "avatar.png" ?: unlink("./profile_img/" . $getFile);
            return response()->json([
                'success' => true,
                'message' => 'delete successfully !'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'later try again _^^_'
            ], 500);
        }
    }


    public function getSingleContact($id)
    {
        $file_dir = $this->baseUrl . "/profile_img";
        $data = $this->contacts::find($id);
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'not found!'
            ], 501);
        } else {
            return response()->json([
                'success' => true,
                'data' => $data,
                'file_dir' => $file_dir
            ], 200);
        }
    }

    public function searchData($search, $token, $paginate = null)
    {
        $fileDir = $this->baseUrl . "/profile_img";
        $user = auth('users')->authenticate($token)->id;
        if ($paginate == null || $paginate == '') {
            $data = $this->contacts::where('userId', $user)->
            where(function ($query) use ($search) {
                $query->where('firstname', 'LIKE', "%$search%")
                    ->orWhere('lastname', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->orWhere('phonenumber', 'LIKE', "%$search%");
            })->orderBy('id', 'DESC')
                ->get()->toArray();
            return response()->json([
                'success' => true,
                'data' => $data,
                'file_dir' => $fileDir
            ], 200);
        } else {
            $data = $this->contacts::where('userId', $user)
                ->where(function ($query) use ($search) {
                    $query->where('firstname', 'LIKE', "%$search%")
                        ->orWhere('lastname', 'LIKE', "%$search%")
                        ->orWhere('phonenumber', 'LIKE', "%$search%")
                        ->orWhere('email', 'LIKE', "%$search%");
                })->orderBy('id', 'DESC')->paginate($paginate);
            return response()->json([
                'success' => true,
                'data' => $data,
                'file_dir' => $fileDir
            ], 200);
        }

    }

}
