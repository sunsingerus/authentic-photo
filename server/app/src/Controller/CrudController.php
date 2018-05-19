<?php

namespace App\Controller;

use App\Common\Helper;
use App\Common\JsonException;
use App\Scopes\MaxPerPageScope;
use Slim\Http\Request;
use Slim\Http\Response;

class CrudController extends BaseController
{
    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function actionIndex(Request $request, Response $response, $args)
    {
        $modelName = 'App\Model\\'.Helper::dashesToCamelCase($args['entity'], true);
        $params    = $request->getQueryParams();
        $query     = $modelName::CurrentUser();

        if (isset($params['withTrashed']) && $params['withTrashed'] == 1) {
            $query = $modelName::withTrashed();
        }

        $filters = $this->getDecodedParams($params, 'filters');
        foreach ($filters as $filter) {
            $filter['operator']  = trim(strtolower($filter['operator']));
            $filter['attribute'] = trim($filter['attribute']);

            if (empty($filter['operator']) || empty($filter['attribute']) || empty($filter['value'])) {
                continue;
            }

            switch ($filter['operator']) {
                case 'in':
                    $query = $query->whereIn($filter['attribute'], $filter['value']);
                    break;
                case 'not in':
                    $query = $query->whereNotIn($filter['attribute'], $filter['value']);
                    break;
                case 'like':
                    $query = $query->where($filter['attribute'], 'like', '%'.$filter['value'].'%');
                    break;
                case '=':
                case '!=':
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $query = $query->where($filter['attribute'], $filter['operator'], $filter['value']);
                    break;
            }
        }

        $sorters = $this->getDecodedParams($params, 'sort');
        foreach ($sorters as $sorter) {
            $sorter['direction'] = trim(strtolower($sorter['direction'])) == 'asc' ? 'asc' : 'desc';
            $query->orderBy(trim($sorter['attribute']), $sorter['direction']);
        }

        $pageNumber = null;
        $pageSize   = null;
        if (isset($params['page']['number'])) {
            $pageNumber = $params['page']['number'];
            $pageSize   = (isset($params['page']['size']) && $params['page']['size'] <= 100) ? $params['page']['size'] : 15;
            $entities   = $query->withoutGlobalScopes([MaxPerPageScope::class])->paginate($pageSize, ['*'], 'page', $pageNumber);
        } else {
            $entities = $query->get();
        }

        $result = $this->encoder->encode($request, $entities, $pageNumber, $pageSize);

        return $this->apiRenderer->jsonResponse($response, 200, $result);
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws JsonException
     */
    public function actionGet(Request $request, Response $response, $args)
    {
        $modelName = 'App\Model\\'.Helper::dashesToCamelCase($args['entity'], true);
        $query     = $modelName::CurrentUser();
        $entity    = $query->find($args['id']);

        if (!$entity) {
            throw new JsonException($args['entity'], 404, 'Not found', 'Entity not found');
        }

        $result = $this->encoder->encode($request, $entity);

        return $this->apiRenderer->jsonResponse($response, 200, $result);
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws JsonException
     */
    public function actionCreate(Request $request, Response $response, $args)
    {
        $modelName    = 'App\Model\\'.Helper::dashesToCamelCase($args['entity'], true);
        $requestClass = 'App\Requests\\'.Helper::dashesToCamelCase($args['entity'], true).'CreateRequest';
        $params       = $request->getParsedBody();

        $this->validateRequestParams($params, $args['entity'], new $requestClass());

        $entity = $modelName::create($params['data']['attributes']);
        $result = $this->encoder->encode($request, $entity);

        return $this->apiRenderer->jsonResponse($response, 200, $result);

    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws JsonException
     */
    public function actionUpdate(Request $request, Response $response, $args)
    {
        $modelName    = 'App\Model\\'.Helper::dashesToCamelCase($args['entity'], true);
        $requestClass = 'App\Requests\\'.Helper::dashesToCamelCase($args['entity'], true).'UpdateRequest';
        $params       = $request->getParsedBody();
        $query        = $modelName::CurrentUser();
        $entity       = $query->find($args['id']);

        if (!$entity) {
            throw new JsonException($args['entity'], 404, 'Not found', 'Entity not found');
        }

        $this->validateRequestParams($params, $args['entity'], new $requestClass());

        $entity->update($params['data']['attributes']);

        $result = $this->encoder->encode($request, $entity);

        return $this->apiRenderer->jsonResponse($response, 200, $result);
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws JsonException
     */
    public function actionDelete(Request $request, Response $response, $args)
    {
        $modelName = 'App\Model\\'.Helper::dashesToCamelCase($args['entity'], true);
        $query     = $modelName::CurrentUser();
        $entity    = $query->find($args['id']);

        if (!$entity) {
            throw new JsonException($args['entity'], 404, 'Not found', 'Entity not found');
        }

        $entity->delete();

        return $this->apiRenderer->jsonResponse($response, 204);
    }

    /**
     * @param array  $params
     * @param string $key
     * @return array|mixed
     */
    private function getDecodedParams($params, $key)
    {
        $decoded = [];
        if (isset($params[$key])) {
            $decoded = json_decode($params[$key], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $decoded = [];
            }
        }

        return $decoded;
    }
}
