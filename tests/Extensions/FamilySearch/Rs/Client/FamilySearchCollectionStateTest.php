<?php

namespace Gedcomx\tests\Extensions\FamilySearch\Rs\Client;

use Gedcomx\Extensions\FamilySearch\Rs\Client\FamilySearchCollectionState;
use Gedcomx\Extensions\FamilySearch\Rs\Client\FamilySearchStateFactory;
use Gedcomx\Rs\Client\CollectionsState;
use Gedcomx\Rs\Client\Util\HttpStatus;
use Gedcomx\Tests\ApiTestCase;

class FamilySearchCollectionStateTest extends ApiTestCase
{
    /**
     * @link https://familysearch.org/developers/docs/api/tree/Read_Current_User_usecase
     */
    public function testReadCurrentUser()
    {
        $factory = new FamilySearchStateFactory();
        $this->collectionState($factory);
        $userState = $this->collectionState()->readCurrentUser();
        $this->assertEquals(HttpStatus::OK, $userState->getResponse()->getStatusCode());
    }

    /**
     * @link https://familysearch.org/developers/docs/api/tree/Read_Current_User_usecase
     */
    public function testReadCurrentUserHistory()
    {
        $factory = new FamilySearchStateFactory();
        $this->collectionState($factory);
        $historyState = $this->collectionState()->readCurrentUserHistory();
        $this->assertEquals(
            HttpStatus::OK,
            $historyState->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__, $historyState)
        );
        $this->assertNotEmpty($historyState->getUserHistory());
    }

    /**
     * @link https://familysearch.org/developers/docs/api/tree/Read_Current_User_usecase
     */
    public function testUpdateCurrentUserHistory()
    {
        $factory = new FamilySearchStateFactory();
        $this->collectionState($factory);
        $historyState = $this->collectionState()->readCurrentUserHistory();
        $stateTwo = $historyState->post($historyState->getEntity());

        $this->assertEquals(
            HttpStatus::NO_CONTENT,
            $stateTwo->getResponse()->getStatusCode(),
            $this->buildFailMessage(__METHOD__, $stateTwo)
        );
    }

    public function testReadASpecificUsersSetOfUserDefinedCollections()
    {
        $factory = new FamilySearchStateFactory();
        /** @var FamilySearchCollectionState $collection */
        $collection = $this->collectionState($factory, "https://sandbox.familysearch.org/platform/collections/sources");
        /** @var CollectionsState $subcollections */
        $subcollections = $collection->readSubcollections();

        $this->assertNotNull($subcollections->ifSuccessful());
        $this->assertEquals(HttpStatus::OK, $subcollections->getResponse()->getStatusCode());
        $this->assertNotNull($subcollections->getCollections());
        $this->assertGreaterThan(0, count($subcollections->getCollections()));
    }
}