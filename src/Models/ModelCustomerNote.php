<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace MpSoft\MpPrivateNotes\Models;

class ModelCustomerNote extends \ObjectModel
{
    public $id;
    public $id_customer_messages;
    public $id_customer;
    public $id_employee;
    public $firstname;
    public $lastname;
    public $message;
    public $deleted;
    public $deleted_by;
    public $deleted_at;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'customer_messages',
        'primary' => 'id_customer_messages',
        'fields' => [
            'id_customer' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'id_employee' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'message' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isCleanHtml',
                'required' => true, 'size' => 16777216,
            ],
            'deleted' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'deleted_by' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'deleted_at' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];

    public static function getCustomerMessageByIdOrder($id_order)
    {
        $order = new \Order($id_order);
        if (!\Validate::isLoadedObject($order)) {
            return [];
        }

        $id_customer = $order->id_customer;

        return self::getCustomerMessages($id_customer);
    }

    public static function getCustomerMessages($id_customer, $show_deleted = false)
    {
        $res = [];
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('a.id_customer_messages')
            ->select('a.id_customer')
            ->select('a.id_employee')
            ->select('e.firstname')
            ->select('e.lastname')
            ->select('a.message')
            ->select('a.deleted')
            ->select('a.deleted_by')
            ->select('a.deleted_at')
            ->select('CONCAT(ed.firstname, \' \', ed.lastname) AS deleted_employee')
            ->select('a.date_add')
            ->from(self::$definition['table'], 'a')
            ->leftJoin('employee', 'e', 'e.id_employee=a.id_employee')
            ->leftJoin('employee', 'ed', 'ed.id_employee=a.deleted_by')
            ->where('a.id_customer=' . (int) $id_customer)
            ->orderBy('a.date_add DESC');

        if (!$show_deleted) {
            $sql->where('a.deleted=0 OR a.deleted IS NULL');
        }

        $res = $db->executeS($sql);

        $id_employee = \Context::getContext()->employee->id;
        $employee = new \Employee($id_employee);
        $customer = new \Customer($id_customer);
        $note = trim($customer->note);

        if ($note) {
            array_unshift(
                $res,
                [
                    'id_customer_messages' => 0,
                    'id_customer' => $id_customer,
                    'id_employee' => 0,
                    'firstname' => '--',
                    'lastname' => '--',
                    'message' => $note,
                    'date_add' => '--',
                ]
            );
        }

        foreach ($res as &$row) {
            $row['date_add'] = \Tools::displayDate($row['date_add'], true);
            if ($employee->isSuperAdmin()) {
                $file = 'module:mpprivatenotes/views/templates/admin/delbutton.tpl';
                $tpl = \Context::getContext()->smarty->createTemplate($file);
                $tpl->assign('id_customer_messages', $row['id_customer_messages']);
                if (!$tpl->isCached($file, 'customer_message_' . $row['id_customer_messages'])) {
                    $row['action'] = $tpl->fetch();
                } else {
                    $row['action'] = $tpl->fetch($file, 'customer_message_' . $row['id_customer_messages']);
                }
            }
        }

        return $res;
    }
}
