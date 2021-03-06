<?php


namespace Gedcomx\Rs\Client;

use Gedcomx\Gedcomx;
use Gedcomx\Conclusion\Person;
use Gedcomx\Rs\Client\Options\StateTransitionOption;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Client;
use RuntimeException;

/**
 * The PersonsState exposes management functions for a persons collection.
 *
 * Class PersonsState
 *
 * @package Gedcomx\Rs\Client
 */
class PersonsState extends GedcomxApplicationState
{
    /**
     * Constructs a new persons state using the specified client, request, response, access token, and state factory.
     * @param \Guzzle\Http\Client             $client
     * @param \Guzzle\Http\Message\Request    $request
     * @param \Guzzle\Http\Message\Response   $response
     * @param string                          $accessToken
     * @param \Gedcomx\Rs\Client\StateFactory $stateFactory
     */
    function __construct(Client $client, Request $request, Response $response, $accessToken, StateFactory $stateFactory)
    {
        parent::__construct($client, $request, $response, $accessToken, $stateFactory);
    }

    /**
     * Clones the current state instance.
     *
     * @param \Guzzle\Http\Message\Request  $request
     * @param \Guzzle\Http\Message\Response $response
     *
     * @return \Gedcomx\Rs\Client\PersonsState
     */
    protected function reconstruct(Request $request, Response $response)
    {
        return new PersonsState($this->client, $request, $response, $this->accessToken, $this->stateFactory);
    }

    /**
     * Returns the entity from the REST API response.
     *
     * @return \Gedcomx\Gedcomx
     */
    protected function loadEntity()
    {
        $json = json_decode($this->response->getBody(), true);
        return new Gedcomx($json);
    }

    /**
     * Gets the main data element represented by this state instance.
     *
     * @return object
     */
    protected function getScope()
    {
        return $this->getEntity();
    }

    /**
     * Gets the array of persons represented by this current instance.
     *
     * @return Person[]|null
     */
    public function getPersons()
    {
        if ($this->entity) {
            return $this->entity->getPersons();
        }

        return null;
    }

    /**
     * Reads the collection specified by this state instance.
     *
     * @param \Gedcomx\Rs\Client\Options\StateTransitionOption $option,...
     *
     * @return \Gedcomx\Rs\Client\CollectionState|null
     */
    public function readCollection(StateTransitionOption $option = null)
    {
        $link = $this->getLink(Rel::COLLECTION);
        if ($link == null || $link->getHref() == null) {
           return null;
        }

        $request = $this->createAuthenticatedGedcomxRequest(Request::GET, $link->getHref());
        return $this->stateFactory->createState(
            'CollectionState',
            $this->getClient(),
            $request,
            $this->passOptionsTo('invoke', array(), func_get_args()),
            $this->accessToken
        );
    }

    /**
     * Adds a person to the current collection.
     *
     * @param Person                                           $person
     * @param \Gedcomx\Rs\Client\Options\StateTransitionOption $option,...
     *
     * @return \Gedcomx\Rs\Client\PersonState|null
     */
    public function addPerson(Person $person, StateTransitionOption $option = null)
    {
        $link = $this->getLink(Rel::SELF);
        $href = $link == null ? null
            : $link->getHref() == null ? null
            : $link->getHref();
        if ($href == null) {
            $href = $this->getSelfUri();
        }

        $request = $this->createAuthenticatedGedcomxRequest(Request::POST, $href);
        return $this->stateFactory->createState(
            'PersonState',
            $this->client,
            $this->request,
            $this->passOptionsTo('invoke', array($request), func_get_args()),
            $this->accessToken
        );
    }
}