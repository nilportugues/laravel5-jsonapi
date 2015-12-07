<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 12/7/15
 * Time: 12:17 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApi\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use NilPortugues\Api\JsonApi\Server\Actions\CreateResource;
use NilPortugues\Api\JsonApi\Server\Actions\DeleteResource;
use NilPortugues\Api\JsonApi\Server\Actions\ListResource;
use NilPortugues\Api\JsonApi\Server\Actions\GetResource;
use NilPortugues\Api\JsonApi\Server\Actions\PatchResource;
use NilPortugues\Api\JsonApi\Server\Actions\PutResource;
use NilPortugues\Laravel5\JsonApi\Eloquent\EloquentHelper;
use NilPortugues\Laravel5\JsonApi\JsonApiSerializer;

/**
 * Class JsonApiController.
 */
abstract class JsonApiController extends Controller
{
    /**
     * @var JsonApiSerializer
     */
    protected $serializer;

    /**
     * @param JsonApiSerializer $serializer
     */
    public function __construct(JsonApiSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $resource = new ListResource($this->serializer);

        $totalAmount = $this->totalAmountResourceCallable();
        $results = $this->listResourceCallable();

        $controllerAction = '\\'.get_class($this).'@listAction';
        $uri = action($controllerAction, []);

        return $resource->get($totalAmount, $results, $uri);
    }

    /**
     * Returns an Eloquent Model.
     *
     * @return Model
     */
    abstract public function getDataModel();

    /**
     * Returns the total number of results available for the current resource.
     *
     * @return callable
     */
    protected function totalAmountResourceCallable()
    {
        return function () {
            $idKey = $this->getDataModel()->getKeyName();

            return $this->getDataModel()->query()->get([$idKey])->count();
        };
    }

    /**
     * Returns a list of resources based on pagination criteria.
     *
     * @return callable
     */
    protected function listResourceCallable()
    {
        return function () {
            return EloquentHelper::paginate($this->serializer, $this->getDataModel()->query())->get();
        };
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(Request $request)
    {
        $find = $this->findResourceCallable($request);
        $resource = new GetResource($this->serializer);

        return $resource->get($request->id, get_class($this->getDataModel()), $find);
    }

    /**
     * @param Request $request
     *
     * @return callable
     */
    protected function findResourceCallable(Request $request)
    {
        return function () use ($request) {
            $idKey = $this->getDataModel()->getKeyName();

            return $this->getDataModel()->query()->where($idKey, $request->id)->first();
        };
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction(Request $request)
    {
        $createResource = $this->createResourceCallable();

        $resource = new CreateResource($this->serializer);

        return $resource->get((array) $request->get('data'), get_class($this->getDataModel()), $createResource);
    }

    /**
     * Reads the input and creates and saves a new Eloquent Model.
     *
     * @return callable
     */
    protected function createResourceCallable()
    {
        return function (array $data, array $values) {
            $model = $this->getDataModel()->newInstance();

            foreach ($values as $attribute => $value) {
                $model->setAttribute($attribute, $value);
            }

            if (!empty($data['id'])) {
                $model->setAttribute($model->getKeyName(), $values['id']);
            }

            $model->save();

            return $model;
        };
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function patchAction(Request $request)
    {
        $find = $this->findResourceCallable($request);
        $update = $this->updateResourceCallable();

        $resource = new PatchResource($this->serializer);

        return $resource->get(
            $request->id,
            (array) $request->get('data'),
            get_class($this->getDataModel()),
            $find,
            $update
        );
    }

    /**
     * @return callable
     */
    protected function updateResourceCallable()
    {
        return function (Model $model, $values) {
            foreach ($values as $attribute => $value) {
                $model->$attribute = $value;
            }
            $model->update();
        };
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putAction(Request $request)
    {
        $find = $this->findResourceCallable($request);
        $update = $this->updateResourceCallable();

        $resource = new PutResource($this->serializer);

        return $resource->get(
            $request->id,
            (array) $request->get('data'),
            get_class($this->getDataModel()),
            $find,
            $update
        );
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request)
    {
        $find = $this->findResourceCallable($request);
        $resource = new DeleteResource($this->serializer);

        return $resource->get($request->id, get_class($this->getDataModel()), $find);
    }
}
