<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeamRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Utils\ErrorType;
use App\Utils\Limits;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    const NAME = 'name';
    const PLAYER_COUNT = 'player_count';
    const REGION = 'region';
    const COUNTRY = 'country';

    public function index()
    {
        $data = Team::query();
        $per_page = Limits::PER_PAGE;

        $page = request()->input('page');

        $count = $data->count();

        if (request()->has('page')) {
            $data = $data->offset(($page - 1) * $per_page)->limit($per_page);
        }
        $data = $data->get();

        return response()->json(["status" => "success", "data" => TeamResource::collection($data), "total" => $count]);
    }

    public function store(TeamRequest $request)
    {
        try {
            $team = new Team();
            $team->name = $request->input(self::NAME);
            $team->player_count = $request->input(self::PLAYER_COUNT);
            $team->region = $request->input(self::REGION);
            $team->country = $request->input(self::COUNTRY);

            $team->save();

            return jsend_success(new TeamResource($team), JsonResponse::HTTP_CREATED);
        } catch (Exception $ex) {
            Log::error(__('api.saved-failed', ['model' => class_basename(Team::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.saved-failed', ['model' => class_basename(Team::class)]), [
                $ex->getCode(),
                ErrorType::SAVE_ERROR,
            ]);
        }
    }

    public function show(Team $team)
    {
        return jsend_success(new TeamResource($team));
    }

    public function update(TeamRequest $request, Team $team)
    {

        try {
            $team->name = $request->input(self::NAME);
            $team->player_count = $request->input(self::PLAYER_COUNT);
            $team->region = $request->input(self::REGION);
            $team->country = $request->input(self::COUNTRY);

            $team->save();

            $team->save();

            return jsend_success(new TeamResource($team), JsonResponse::HTTP_CREATED);
        } catch (Exception $ex) {
            Log::error(__('api.updated-failed', ['model' => class_basename(Team::class)]), [
                'code' => $ex->getCode(),
                'trace' => $ex->getTrace(),
            ]);

            return jsend_error(__('api.updated-failed', ['model' => class_basename(Team::class)]), [
                $ex->getCode(),
                ErrorType::UPDATE_ERROR,
            ]);
        }
    }

    public function destroy(Team $team)
    {
        try {
            $team->delete();

            return jsend_success(null, JsonResponse::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $exception) {
            return jsend_error(["error" => 'Data Not Found.'], JsonResponse::HTTP_NOT_FOUND);
        }
    }
}
