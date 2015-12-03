<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 12/2/15
 * Time: 9:38 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApi\Actions;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use NilPortugues\Api\JsonApi\Server\Errors\ErrorBag;
use NilPortugues\Api\JsonApi\Server\Errors\NotFoundError;
use NilPortugues\Laravel5\JsonApi\Actions\Traits\RequestTrait;
use NilPortugues\Laravel5\JsonApi\Actions\Traits\ResponseTrait;
use NilPortugues\Serializer\Serializer;

/**
 * Class DeleteResource.
 */
class DeleteResource
{
    use RequestTrait;
    use ResponseTrait;

    /**
     * @var ErrorBag
     */
    private $errorBag;

    /**
     * @var \NilPortugues\Serializer\Serializer
     */
    private $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
        $this->errorBag = new ErrorBag();
    }

    /**
     * @param         $id
     * @param Builder $query
     *
     * @return mixed
     */
    public function get($id, Builder $query)
    {
        try {
            $idKey = $query->getModel()->getKeyName();
            $model = $query->getModel()->query()->where($idKey, '=', $id);
            $model->delete();

            return $this->resourceDeleted();
        } catch (Exception $e) {
            $className = get_class($query->getModel());
            $mapping = $this->serializer->getTransformer()->getMappingByClassName($className);

            $errors = new ErrorBag([new NotFoundError($mapping->getClassAlias(), $id)]);

            return $this->resourceNotFound($errors);
        }
    }
}
