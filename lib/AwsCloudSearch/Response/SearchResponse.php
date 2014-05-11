<?php

namespace AwsCloudSearch\Response;

class SearchResponse extends AbstractResponse
{
    private $searchedFields;

    private $hits;
    
    protected $numberOfHits;
    
    protected $startNumber;

    public function __construct(Array $data)
    {
        parent::__construct($data);

        if (isset($data['returnFields'])) {
            $this->searchedFields = $data['returnFields'];
        }
        else {
            $this->searchedFields = array('id');
        }
    }

    public function parseResponse()
    {
        parent::parseResponse();

        if (!$this->wasSuccessful()) {
            return;
        }

        $hits = $this->parsedData->hits->hit;

        if (0 === count($hits)) {
            $this->hits = null;
            $this->numberOfHits = 0;
            $this->startNumber = null;
            return;
        }
        
        $this->numberOfHits = $this->parsedData->hits->found;
        $this->startNumber = $this->parsedData->hits->start;

        $returnHits = array();
        foreach ($hits as $hit) {
            if (isset($hit->data)) {
                $returnHits[$hit->id] = $hit->data;
            }
            else {
                $returnHits[$hit->id] = $hit->id;
            }
        }

        foreach ($returnHits as $key => $hit) {
            if (is_object($hit)) {
                foreach ($hit as $dataKey => $data) {
                    $returnHits[$key]->{$dataKey} = (!empty($data) ? $data[0] : null);
                }
                $returnHits[$key]->_id = $key;
            }
        }

        $this->hits = $returnHits;
    }

    public function getHitDocuments()
    {
        if (!$this->wasSuccessful()) {
            throw new \Exception('Unsuccessful search can not return an array');
        }

        return $this->hits;
    }
    
    /**
     * Returns the number of hits in the result set
     * @return int
     * @throws \Exception
     */
    public function getHitCount()
    {
        if (!$this->wasSuccessful()) {
            throw new \Exception('Unsuccessful search can not return an array');
        }
        
        return $this->numberOfHits;
    }
    
    /**
     * Returns the starting point of the result set
     * @return int
     * @throws \Exception
     */
    public function getStartNumber()
    {
        if (!$this->wasSuccessful()) {
            throw new \Exception('Unsuccessful search can not return an array');
        }
        
        return $this->startNumber;
    }

    public function __toString()
    {
        if ($this->wasSuccessful()) {
            $returnString = '';

            $searchField = $this->searchedFields[0];

            foreach ($this->parsedData->hits->hit as $hit) {
                $returnString .= $hit->data->{$searchField} . ', ';
            }
            $returnString = rtrim($returnString, ', ');
            return $returnString;
        }
        else {
            return 'No search results';
        }
    }
}