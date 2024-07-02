<?php


class MongoDBHandler extends AbstractProcessingHandler {
    protected $mongoCollection;

    public function __construct($mongo, $database, $collection, $level = Logger::DEBUG, $bubble = true)
    {
        if (!($mongo instanceof \MongoClient || $mongo instanceof \Mongo)) {
            throw new \InvalidArgumentException('MongoClient or Mongo instance required');
        }

        $this->mongoCollection = $mongo->selectCollection($database, $collection);

        parent::__construct($level, $bubble);
    }

    protected function write(array $record)
    {
        $this->mongoCollection->save($record["formatted"]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter()
    {
        return new NormalizerFormatter();
    }
}
