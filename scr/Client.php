<?php
/**
 * ---------------------------------------------------------------------
 * @author Vladislav Dneprov <vladislav.dneprov1995@gmail.com>
 * @link https://www.linkedin.com/in/vladislav-dneprov/ Linkedin profile
 * @link https://github.com/kialex Github
 * ---------------------------------------------------------------------
 *
 * @version 1.0.0
 * @package express-test
 */

namespace Kialex\TranslateCenter;

use Translate\ApiClient;
use Translate\Storage\ArrayStorage;
use yii\base\{BaseObject};
use Translate\StorageManager\Contracts\Api;

/**
 * Class Client
 * @see http://dev-api.translate.center/api-docs/
 */
class Client extends BaseObject implements Api
{
    /**
     * @var string Translate Center login
     */
    public $login;

    /**
     * @var string Translate Center password
     */
    public $password;

    /**
     * @var string Translate Center project ID
     */
    public $projectUuid;

    /**
     * @var int Default size for fetching resources
     */
    public $pageSize = 300;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function init()
    {
        parent::init();

        $this->apiClient = new ApiClient([
            'login' => $this->login,
            'password' => $this->password
        ], new ArrayStorage());
        $this->apiClient->setAlias('projectUuid', $this->projectUuid);
    }

    /**
     * @param array $queryParams
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @see http://dev-api.translate.center/api-docs/#/Resource/ResourceGetList
     */
    public function getResources($queryParams = [])
    {
        $this->apiClient->reauthenticate();
        $response = $this->apiClient->request('GET', $this->transformUri('projects/{projectUuid}/resources'), [
            'query' => $queryParams
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @param array $queryParams
     * @return bool[]
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @see http://dev-api.translate.center/api-docs/#/Resource/ResourceGetList
     */
    public function getTags($queryParams = [])
    {
        $response = $this->apiClient->request('GET', $this->transformUri('/projects/{projectUuid}/resource-tags'), [
            'query' => $queryParams
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @param array $params
     * @param int $page
     * @return array
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @see http://dev-api.translate.center/api-docs/#/Resource/ResourceGetList
     */
    public function fetch(array $params = [], int $page = 1): array
    {
        $params['pageSize'] = $params['pageSize'] ?? $this->pageSize;
        $params['pageNum'] = $page;

        $response = $this->apiClient->request('GET', $this->transformUri('projects/{projectUuid}/resources'), [
            'query' => $params
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @param string $key
     * @param array $queryParams
     * @return array
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @see http://dev-api.translate.center/api-docs/#/Resource/ResourceGetByKey
     */
    public function getResource($key, $queryParams = [])
    {
        $response = $this->apiClient->request('GET', $this->transformUri('projects/{projectUuid}/resources/{key}', [
            '/{key}/' => $key
        ]), ['query' => $queryParams]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @param string $key
     * @param array $queryParams
     * @return array
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @see http://dev-api.translate.center/api-docs/#/Resource/ResourceGetTags
     */
    public function getResourceTags($key, $queryParams = [])
    {
        $response = $this->apiClient->request('GET', $this->transformUri('projects/{projectUuid}/resources-tags', [
            '/{key}/' => $key
        ]), ['query' => $queryParams]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @param string $key
     * @param string $lang
     * @param array $data
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @see http://dev-api.translate.center/api-docs/#/Resource/ResourceUpdateResource
     */
    public function updateResource($key, $lang, $data)
    {
        $response = $this->apiClient->request('PUT', $this->transformUri('projects/{projectUuid}/{langCode}/resources/{key}', [
            '/{key}/' => $key,
            '/{langCode}/' => $lang
        ]), ['json' => $data]);

        return $response->getStatusCode() === 200;
    }

    /**
     * @param string $lang
     * @param array $data
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @see http://dev-api.translate.center/api-docs/#/Resource/ResourceAddList
     */
    public function createResource($data, $lang)
    {
        $response = $this->apiClient->request('POST', $this->transformUri('projects/{projectUuid}/{langCode}/resources', [
            '/{langCode}/' => $lang
        ]), ['json' => $data]);

        return $response->getStatusCode() === 200;
    }

    /**
     * @param string $key
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @see http://dev-api.translate.center/api-docs/#/Language/ProjectDeleteLanguageFromProject
     */
    public function deleteResource($key)
    {
        $response = $this->apiClient->request('DELETE', $this->transformUri('projects/{projectUuid}/resources/{key}', [
            '/{key}/' => $key
        ]));

        return $response->getStatusCode() === 200;
    }

    /**
     * @param $uri
     * @param array $keys
     * @return string
     */
    protected function transformUri($uri, $keys = [])
    {
        return preg_replace(array_keys($keys), array_values($keys), $uri);
    }
}
