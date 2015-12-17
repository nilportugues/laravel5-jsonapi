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
use NilPortugues\Api\JsonApi\Http\Factory\RequestFactory;
use NilPortugues\Api\JsonApi\Server\Actions\CreateResource;
use NilPortugues\Api\JsonApi\Server\Actions\DeleteResource;
use NilPortugues\Api\JsonApi\Server\Actions\ListResource;
use NilPortugues\Api\JsonApi\Server\Actions\GetResource;
use NilPortugues\Api\JsonApi\Server\Actions\PatchResource;
use NilPortugues\Api\JsonApi\Server\Actions\PutResource;
use NilPortugues\Api\JsonApi\Server\Errors\Error;
use NilPortugues\Api\JsonApi\Server\Errors\ErrorBag;
use NilPortugues\Laravel5\JsonApi\Eloquent\EloquentHelper;
use NilPortugues\Laravel5\JsonApi\JsonApiSerializer;
use Symfony\Component\HttpFoundation\Response;

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
     * @param int $pageSize
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($pageSize = 10)
    {
        $apiRequest = RequestFactory::create();
        $page = $apiRequest->getPage();

        if (!$page->size()) {
            $page->setSize($pageSize);
        }

        $resource = new ListResource(
            $this->serializer,
            $page,
            $apiRequest->getFields(),
            $apiRequest->getSort(),
            $apiRequest->getIncludedRelationships(),
            $apiRequest->getFilters()
        );

        $response = $resource->get(
            $this->totalAmountResourceCallable(),
            $this->listResourceCallable(),
            action('\\'.get_class($this).'@listAction', []),
            get_class($this->getDataModel())
        );

        return $this->addHeaders($response);
    }

    /**
     * Returns the total number of results available for the current resource.
     *
     * @return callable
     * @codeCoverageIgnore
     */
    protected function totalAmountResourceCallable()
    {
        return function () {
            $idKey = $this->getDataModel()->getKeyName();

            return $this->getDataModel()->query()->get([$idKey])->count();
        };
    }

    /**
     * Returns an Eloquent Model.
     *
     * @return Model
     */
    abstract public function getDataModel();

    /**
     * Returns a list of resources based on pagination criteria.
     *
     * @return callable
     * @codeCoverageIgnore
     */
    protected function listResourceCallable()
    {
        return function () {
            return EloquentHelper::paginate($this->serializer, $this->getDataModel()->query())->get();
        };
    }

    /**
     * @param Response $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function addHeaders(Response $response)
    {
        return $response;
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(Request $request)
    {
        $apiRequest = RequestFactory::create();

        $resource = new GetResource(
            $this->serializer,
            $apiRequest->getFields(),
            $apiRequest->getIncludedRelationships()
        );

        $response = $resource->get(
            $request->id,
            get_class($this->getDataModel()),
            $this->findResourceCallable($request)
        );

        return $this->addHeaders($response);
    }

    /**
     * @param Request $request
     *
     * @return callable
     * @codeCoverageIgnore
     */
    protected function findResourceCallable(Request $request)
    {
        return function () use ($request) {
            $idKey = $this->getDataModel()->getKeyName();
            $model = $this->getDataModel()->query()->where($idKey, $request->id)->first();

            return $model;
        };
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction(Request $request)
    {
        $resource = new CreateResource($this->serializer);

        $response = $resource->get(
            (array) $request->get('data'),
            get_class($this->getDataModel()),
            $this->createResourceCallable()
        );

        return $this->addHeaders($response);
    }

    /**
     * Reads the input and creates and saves a new Eloquent Model.
     *
     * @return callable
     * @codeCoverageIgnore
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

            try {
                $model->save();
            } catch (\Exception $e) {
                $errorBag[] = new Error('creation_error', 'Resource could not be created');
                throw $e;
            }

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
        $resource = new PatchResource($this->serializer);

        $response = $resource->get(
            $request->id,
            (array) $request->get('data'),
            get_class($this->getDataModel()),
            $this->findResourceCallable($request),
            $this->updateResourceCallable()
        );

        return $this->addHeaders($response);
    }

    /**
     * @return callable
     * @codeCoverageIgnore
     */
    protected function updateResourceCallable()
    {
        return function (Model $model, array $values, ErrorBag $errorBag) {
            foreach ($values as $attribute => $value) {
                $model->$attribute = $value;
            }
            try {
                $model->update();
            } catch (\Exception $e) {
                $errorBag[] = new Error('update_failed', 'Could not update resource.');
                throw $e;
            }
        };
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putAction(Request $request)
    {
        $resource = new PutResource($this->serializer);

        $response = $resource->get(
            $request->id,
            (array) $request->get('data'),
            get_class($this->getDataModel()),
            $this->findResourceCallable($request),
            $this->updateResourceCallable()
        );

        return $this->addHeaders($response);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request)
    {
        $resource = new DeleteResource($this->serializer);

        $response = $resource->get(
            $request->id,
            get_class($this->getDataModel()),
            $this->findResourceCallable($request),
            $this->deleteResourceCallable($request)
        );

        return $this->addHeaders($response);
    }

    /**
     * @param Request $request
     *
     * @return callable
     * @codeCoverageIgnore
     */
    protected function deleteResourceCallable(Request $request)
    {
        return function () use ($request) {
            $idKey = $this->getDataModel()->getKeyName();
            $model = $this->getDataModel()->query()->where($idKey, $request->id)->first();

            return $model->delete();
        };
    }
}
