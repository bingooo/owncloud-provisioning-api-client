<?php

namespace Arnovr\OwncloudProvisioning;

use Arnovr\OwncloudProvisioning\Command\AddUserToGroup;
use Arnovr\OwncloudProvisioning\Command\CreateGroup;
use Arnovr\OwncloudProvisioning\Command\CreateUser;
use Arnovr\OwncloudProvisioning\Command\DeleteGroup;
use Arnovr\OwncloudProvisioning\Command\DeleteUser;
use Arnovr\OwncloudProvisioning\Command\DeleteUserFromGroup;
use Arnovr\OwncloudProvisioning\Command\EditUser;
use Arnovr\OwncloudProvisioning\Command\FindGroups;
use Arnovr\OwncloudProvisioning\Command\FindGroupsOfUser;
use Arnovr\OwncloudProvisioning\Command\FindSubAdminGroupsOfUser;
use Arnovr\OwncloudProvisioning\Command\FindUser;
use Arnovr\OwncloudProvisioning\Command\FindUsers;
use Arnovr\OwncloudProvisioning\Command\FindUsersOfGroup;
use Arnovr\OwncloudProvisioning\Command\MakeUserSubAdminOfGroup;
use Arnovr\OwncloudProvisioning\Command\RemoveUsersSubAdminRightsFromGroup;
use Arnovr\OwncloudProvisioning\ResponseParser\ResponseParser;
use Arnovr\OwncloudProvisioning\Result\Exception\NothingToModifyException;
use Arnovr\OwncloudProvisioning\Result\GroupsList;
use Arnovr\OwncloudProvisioning\Result\User;
use Arnovr\OwncloudProvisioning\Result\UserList;
use Arnovr\OwncloudProvisioning\Result\StatusResult;

class ProvisioningClient
{
    /**
     * @var ApiConnection
     */
    private $apiConnection;

    /**
     * @var ResponseParser
     */
    private $responseParser;

    /**
     * ProvisioningClient constructor.
     * @param ApiConnection $apiConnection
     * @param ResponseParser $responseParser
     */
    public function __construct(ApiConnection $apiConnection, ResponseParser $responseParser)
    {
        $this->apiConnection = $apiConnection;
        $this->responseParser = $responseParser;
    }

    /**
     * @param CreateUser $createUserCommand
     * @return StatusResult
     */
    public function createUser(CreateUser $createUserCommand)
    {
        $body = [
            'userid' => $createUserCommand->getUserName(),
            'password' => $createUserCommand->getPassword()
        ];
        $apiResponse = $this->apiConnection->sendRequest("POST", "/users", http_build_query($body));

        return $this->responseParser->parseResponse($apiResponse);
    }

    /**
     * @param EditUser $editUserCommand
     * @return StatusResult
     *
     * @throws NothingToModifyException
     */
    public function editUser(EditUser $editUserCommand)
    {
        $body = get_object_vars($editUserCommand);
        foreach($body as $key => $value)
        {
            if (empty($value)) {
                unset($body[$key]);
            }
        }

        if (count($body) === 0) {
            throw new NothingToModifyException('Nothing is specified to be modified');
        }

        $apiResponse = $this->apiConnection->sendRequest("PUT", "/users/" . $editUserCommand->getUserName(), http_build_query($body));

        return $this->responseParser->parseResponse($apiResponse);
    }

    /**
     * @param DeleteUser $deleteUserCommand
     * @return StatusResult
     */
    public function deleteUser(DeleteUser $deleteUserCommand)
    {
        $apiResponse = $this->apiConnection->sendRequest("DELETE", "/users/" . $deleteUserCommand->getUserName(), '');

        return $this->responseParser->parseResponse($apiResponse);
    }

    /**
     * @param FindUsers $findUsersCommand
     * @return UserList
     */
    public function findUsers(FindUsers $findUsersCommand)
    {
        $apiResponse = $this->apiConnection->sendRequest("GET", "/users?search=" . $findUsersCommand->getUserName(), '');

        return $this->responseParser->parseFindUsers($apiResponse);
    }

    /**
     * @param FindUser $findUserCommand
     * @return User
     */
    public function findUser(FindUser $findUserCommand)
    {
        $apiResponse = $this->apiConnection->sendRequest("GET", "/users/" . $findUserCommand->getUserName(), '');

        return $this->responseParser->parseFindUser($apiResponse);
    }

    /**
     * @param AddUserToGroup $addUserToGroup
     * @return StatusResult
     */
    public function addUserToGroup(AddUserToGroup $addUserToGroup)
    {
        $body = [
            'groupid' => $addUserToGroup->getGroupId()
        ];

        $apiResponse = $this->apiConnection->sendRequest("POST", "/users/" . $addUserToGroup->getUserName() . '/groups', http_build_query($body));

        return $this->responseParser->parseResponse($apiResponse);
    }

    /**
     * @param FindGroupsOfUser $findGroupsOfUser
     * @return GroupsList
     */
    public function findGroupOfUsers(FindGroupsOfUser $findGroupsOfUser)
    {
        $apiResponse = $this->apiConnection->sendRequest("GET", "/users/" . $findGroupsOfUser->getUserName() . '/groups', '');

        return $this->responseParser->parseFindGroup($apiResponse);
    }

    /**
     * @param CreateGroup $addGroupCommand
     * @return StatusResult
     */
    public function createGroup(CreateGroup $addGroupCommand)
    {
        $body = [
            'groupid' => $addGroupCommand->getGroupId()
        ];

        $apiResponse = $this->apiConnection->sendRequest("POST", "/groups", http_build_query($body));

        return $this->responseParser->parseResponse($apiResponse);
    }

    /**
     * @param DeleteGroup $deleteGroupCommand
     * @return StatusResult
     */
    public function deleteGroup(DeleteGroup $deleteGroupCommand)
    {
        $apiResponse = $this->apiConnection->sendRequest("DELETE", "/groups/" . $deleteGroupCommand->getGroupId(), '');

        return $this->responseParser->parseResponse($apiResponse);
    }

    /**
     * @param DeleteUserFromGroup $deleteUserFromGroup
     * @return StatusResult
     */
    public function deleteUserFromGroup(DeleteUserFromGroup $deleteUserFromGroup)
    {
        $body = [
            'groupid' => $deleteUserFromGroup->getGroupId()
        ];
        $apiResponse = $this->apiConnection->sendRequest("DELETE", "/users/" . $deleteUserFromGroup->getUserName() . "/groups", http_build_query($body));
        return $this->responseParser->parseResponse($apiResponse);
    }


    /**
     * @param MakeUserSubAdminOfGroup $makeUserSubAdminOfGroup
     * @return StatusResult
     */
    public function makeUserSubAdminOfGroup(MakeUserSubAdminOfGroup $makeUserSubAdminOfGroup)
    {
        $body = [
            'groupid' => $makeUserSubAdminOfGroup->getGroupId()
        ];
        $apiResponse = $this->apiConnection->sendRequest("POST", "/users/" . $makeUserSubAdminOfGroup->getUserName() . "/subadmins", http_build_query($body));
        return $this->responseParser->parseResponse($apiResponse);
    }

    /**
     * @param RemoveUsersSubAdminRightsFromGroup $removeUsersSubAdminRightsFromGroup
     * @return StatusResult
     */
    public function removeUsersSubAdminRightsFromGroup(RemoveUsersSubAdminRightsFromGroup $removeUsersSubAdminRightsFromGroup)
    {
        $body = [
            'groupid' => $removeUsersSubAdminRightsFromGroup->getGroupId()
        ];
        $apiResponse = $this->apiConnection->sendRequest("DELETE", "/users/" . $removeUsersSubAdminRightsFromGroup->getUserName() . "/subadmins", http_build_query($body));
        return $this->responseParser->parseResponse($apiResponse);
    }

    /**
     * @param FindSubAdminGroupsOfUser $findSubAdminGroupsOfUser
     * @return GroupsList
     */
    public function findSubAdminGroupsOfUser(FindSubAdminGroupsOfUser $findSubAdminGroupsOfUser)
    {
        $apiResponse = $this->apiConnection->sendRequest("GET", "/users/" . $findSubAdminGroupsOfUser->getUserName() . "/subadmins", http_build_query($body));
        return $this->responseParser->parseFindGroup($apiResponse);
    }

    /**
     * @param FindGroups $findGroups
     * @return GroupsList
     */
    public function findGroups(FindGroups $findGroups)
    {
        $apiResponse = $this->apiConnection->sendRequest("GET", "/groups?search=" . $findGroups->getSearchGroup(), "");
        return $this->responseParser->parseFindGroup($apiResponse);
    }

    /**
     * @param FindUsersOfGroup $findUsersOfGroup
     * @return UserList
     */
    public function findUsersOfGroup(FindUsersOfGroup $findUsersOfGroup)
    {
        $apiResponse = $this->apiConnection->sendRequest("GET", "/groups/" . $findUsersOfGroup->getGroupId(), "");
        return $this->responseParser->parseFindUsers($apiResponse);
    }
}