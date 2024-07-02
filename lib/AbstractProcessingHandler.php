<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2021/1/22
 * Time: 10:21 AM
 * Brief:
namespace Monolog\Handler;
 * Base Handler class providing the Handler structure
 *
 * Classes extending it should (in most cases) only implement write($record)
 *
 */
abstract class AbstractProcessingHandler extends AbstractHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        $record = $this->processRecord($record);

        $record['formatted'] = $this->getFormatter()->format($record);

        $this->write($record);

        return false === $this->bubble;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    abstract protected function write(array $record);

    /**
     * Processes a record.
     *
     * @param  array $record
     * @return array
     */
    protected function processRecord(array $record)
    {
        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }

        return $record;
    }
}
