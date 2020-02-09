<?php

use PHPUnit\Framework\TestCase;
use StraceOutUtils\Processor\MysqlQueryResult;

class MysqlQueryResultTest extends TestCase
{
    public function testProcess()
    {
        $processor = new MysqlQueryResult;

        $parts = [
            'call' => 'close',
            'args' => '4'
        ];
        $result = $processor->process($parts);
        $this->assertEquals($result, false);

        $parts = [
            'call' => 'sendto',
            'args' => '3, ".\0\0\0\3SELECT `store_website`.* FROM `store_website`", 50, MSG_DONTWAIT, NULL, 0'
        ];
        $result = $processor->process($parts);
        $this->assertEquals($result, false);

        $parts = [
            'call' => 'poll',
            'args' => '[{fd=3, events=POLLIN|POLLERR|POLLHUP}], 1, 86400000'
        ];
        $result = $processor->process($parts);
        $this->assertEquals($result, false);

        $parts = [
            'call' => 'recvfrom',
            'args' => '3, "\1\0\0\1\6T\0\0\2\3def\20magento_2_3_4_ee\rstore_website\rstore_website\nwebsite_id\nwebsite_id\f?\0\5\0\0\0\2#B\0\0\0H\0\0\3\3def\20magento_2_3_4_ee\rstore_w", 126, MSG_DONTWAIT, NULL, NULL'
        ];
        $result = $processor->process($parts);
        $this->assertEquals($result, false);

        $parts = [
            'call' => 'poll',
            'args' => '[{fd=3, events=POLLIN|POLLERR|POLLHUP}], 1, 86400000'
        ];
        $result = $processor->process($parts);
        $this->assertEquals($result, false);

        $parts = [
            'call' => 'recvfrom',
            'args' => '3, "ebsite\rstore_website\4code\4code\f!\0`\0\0\0\375\4@\0\0\0H\0\0\4\3def\20magento_2_3_4_ee\rstore_website\rstore_website\4name\4name\f!\0\300\0\0\0\375\0\0\0\0\0T\0\0\5\3def\20magento_2_3_4_ee\rstore_website\rstore_website\nsort_order\nsort_", 189, MSG_DONTWAIT, NULL, NULL'
        ];
        $result = $processor->process($parts);
        $this->assertEquals($result, false);

        $parts = [
            'call' => 'sendto',
            'args' => '"%\0\0\0\3SHOW TABLE STATUS LIKE store_group", 41, MSG_DONTWAIT, NULL, 0'
        ];
        $result = $processor->process($parts);
        $this->assertEquals($result, '\1\0\0\1\6T\0\0\2\3def\20magento_2_3_4_ee\rstore_website\rstore_website\nwebsite_id\nwebsite_id\f?\0\5\0\0\0\2#B\0\0\0H\0\0\3\3def\20magento_2_3_4_ee\rstore_website\rstore_website\4code\4code\f!\0`\0\0\0\375\4@\0\0\0H\0\0\4\3def\20magento_2_3_4_ee\rstore_website\rstore_website\4name\4name\f!\0\300\0\0\0\375\0\0\0\0\0T\0\0\5\3def\20magento_2_3_4_ee\rstore_website\rstore_website\nsort_order\nsort_');

        $parts = [
            'call' => 'poll',
            'args' => '[{fd=3, events=POLLIN|POLLERR|POLLHUP}], 1, 86400000'
        ];
        $result = $processor->process($parts);
        $this->assertEquals($result, false);

        $parts = [
            'call' => 'recvfrom',
            'args' => '3, "\1\0\0\1\22B\0\0\2\3def\22information_schema\6TABLES\6TAB", 43, MSG_DONTWAIT, NULL, NULL'
        ];
        $result = $processor->process($parts);
        $this->assertEquals($result, false);

        $parts = [
            'call' => 'poll',
            'args' => '[{fd=3, events=POLLIN|POLLERR|POLLHUP}], 1, 86400000'
        ];
        $result = $processor->process($parts);
        $this->assertEquals($result, false);

        $parts = [
            'call' => 'recvfrom',
            'args' => '3, "LES\4Name\nTABLE_NAME\f!\0\300\0\0\0\375\1\0\0\0\0@\0\0\3\3def\22information_schema\6TABLES\6TABLES\6Engine\6EN", 83, MSG_DONTWAIT, NULL, NULL'
        ];
        $result = $processor->process($parts);
        $this->assertEquals($result, false);

        $parts = [
            'call' => 'access',
            'args' => '"/var/www/magento-2-3-4-ee/vendor/composer/../../lib/internal/Magento/Framework/DB/Select.php", F_OK'
        ];

        $result = $processor->process($parts);
        $this->assertEquals($result, '\1\0\0\1\22B\0\0\2\3def\22information_schema\6TABLES\6TABLES\4Name\nTABLE_NAME\f!\0\300\0\0\0\375\1\0\0\0\0@\0\0\3\3def\22information_schema\6TABLES\6TABLES\6Engine\6EN');
    }
}
