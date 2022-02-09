<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Api pour scrabble",
 *      description="Implementation de Swagger avec Laravel9 pour documneter l'api",
 *      @OA\Contact(
 *          email="admin@admin.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="https://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Demo API Server"
 * )
 * @OA\Tag(
 *     name="ScrabbleGO",
 *     description="API Endpoints de projet"
 * )
 * @OA\Get(
 *     path="/projects",
 *     @OA\Response(response="200", description="Display a listing of projects.")
 * )
 *
 *
 */
class Controller extends BaseController
{


    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
