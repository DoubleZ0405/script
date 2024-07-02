<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2021/1/22
 * Time: 10:20 AM
 * Brief: Logs to a MongoDB database.
 * usage example:
 *
 *   $log = new Logger('application');
 *   $mongodb = new MongoDBHandler(new \Mongo("mongodb://localhost:27017"), "logs", "prod");
 *   $log->pushHandler($mongodb);
 *
 */

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
