<?php
// order_review.inc.php - Sample legacy code for migration assessment
// Note: This represents our typical code patterns and complexity level

$dbGET_ORDER = $db->fetch_assoc($db->query('
    SELECT
        o.order_id, o.customer_id, o.store_id,
        o.product_id, o.workflow_id, o.template_id, o.id, o.title, o.notes, o.dt_created, o.created_by, o.dt_required, o.dt_deadline, o.dt_completed, o.completed_by, o.dt_approved, o.dt_shipped, o.shipped_by, o.dt_cancelled, o.cancelled_by, o.cancel_reason,
        o.assigned_to AS assigned_user_id, CONCAT(u1.firstname, \' \', u1.lastname) AS assigned_user,
        o.approved_by, CONCAT(u2.firstname, \' \', u2.lastname) AS approver_name,
        o.shipped_by, CONCAT(u3.firstname, \' \', u3.lastname) AS shipper_name,
        o.cancelled_by, CONCAT(u4.firstname, \' \', u4.lastname) AS canceller_name,
        CASE
            WHEN o.dt_cancelled > 0 AND o.dt_shipped > 0 THEN \'Cancelled\'
            WHEN o.dt_shipped > 0 THEN \'Shipped\'
            WHEN o.dt_approved > 0 THEN \'Approved\'
            WHEN o.dt_deadline > 0 AND '.time().' > o.dt_deadline THEN \'Overdue\'
            WHEN o.dt_required > 0 AND '.time().' > o.dt_required THEN \'Late\'
            ELSE \'Pending\'
        END as status,
        CASE WHEN o.assigned_to = '.$_SESSION['user']['user_id'].' THEN 1 ELSE 0 END AS is_mine,
        CASE WHEN EXISTS (SELECT user_id FROM app_permissions WHERE order_id = o.order_id AND module = \''.$APP_CONFIG['module'].'\' AND permission_type = \'manager\' AND user_id = '.$_SESSION['user']['user_id'].' AND can_approve = 1) THEN 1 ELSE 0 END AS can_approve
    FROM
        '.$APP_CONFIG['orders_table'].' o
        LEFT OUTER JOIN user_accounts u1 ON u1.user_id = o.assigned_to
        LEFT OUTER JOIN user_accounts u2 ON u2.user_id = o.approved_by
        LEFT OUTER JOIN user_accounts u3 ON u3.user_id = o.shipped_by
        LEFT OUTER JOIN user_accounts u4 ON u4.user_id = o.cancelled_by
    WHERE
        o.'.$APP_CONFIG['primary_key'].' = '.$order_id.'
'));

$dbGET_PRODUCT = $db->fetch_assoc($db->query('
    SELECT
        p.product_id, p.owner_id, p.name, wt.workflow_name, wt.icon,
        CONCAT(ua.firstname, \' \', ua.lastname) AS product_owner
    FROM
        products p
        JOIN workflow_templates wt ON wt.template_id = p.template_id
        LEFT OUTER JOIN user_accounts ua ON ua.user_id = p.owner_id
    WHERE
        p.product_id = '.$dbGET_ORDER['product_id']
));

include($_SERVER['DOCUMENT_ROOT'].'/app/modules/orders/components/validations/check_incomplete.php');

$dbGET_TEMPLATE = $db->fetch_assoc($db->query('SELECT system_id, company_id, store_id, template_id, template_name, intro_text, settings, approval_required FROM workflow_templates WHERE template_id = '.$order_template_id));
$dbGET_TEMPLATE['settings'] = json_decode($dbGET_TEMPLATE['settings'], true);
$dbGET_STORE_SETTINGS = $db->fetch_assoc($db->query('SELECT approval_style FROM '.($dbGET_TEMPLATE['store_id'] ? 'stores' : 'companies').' WHERE company_id = '.$dbGET_TEMPLATE['company_id'].($dbGET_TEMPLATE['store_id'] ? ' AND store_id='.$dbGET_TEMPLATE['store_id'] : false)));

$dbGET_WORKFLOW = $db->fetch_assoc($db->query('SELECT workflow_id, workflow_data FROM '.$APP_CONFIG['workflow_table'].' WHERE workflow_id = '.$dbGET_ORDER['workflow_id']));
$dbGET_WORKFLOW['workflow_data'] = ($dbGET_WORKFLOW['workflow_data'] ?? null);
$dbGET_WORKFLOW['workflow_data'] = json_decode($dbGET_WORKFLOW['workflow_data'], true);

$ORDER_FIELDS = array();
$dbGET_FIELD = $db->fetch_row($db->query('SELECT field_id FROM template_fields WHERE template_id = '.$order_template_id.' AND settings LIKE \'%s:6:"column";s:12:"dt_completed"%\''));
$ORDER_FIELDS['completion_date'] = $dbGET_FIELD[0];
$dbGET_FIELD = $db->fetch_row($db->query('SELECT field_id FROM template_fields WHERE template_id = '.$order_template_id.' AND field_type = \'Priority Level\''));
$ORDER_FIELDS['priority'] = $dbGET_FIELD[0];
?>

<form method="post" action="/<?=$_SESSION['app']['current_page']?>?<?=$query_string?>">
    <div class="table-responsive mb-3">
        <table class="table table-bordered">
            <tr>
                <td width="10%">Assigned To</td>
                <td width="40%"><?=($dbGET_ORDER['assigned_user_id'] ? show_user_avatar($dbGET_ORDER['assigned_user_id']).'<span class="notranslate">'.$dbGET_ORDER['assigned_user'].'</span>' : 'Unassigned')?></td>
                <td width="10%" class="d-none d-lg-table-cell">Product</td>
                <td width="40%" class="d-none d-lg-table-cell"><?=(check_user_permission('Products', $dbGET_ORDER['product_id']) ? '<a href="/products?product_id='.$dbGET_ORDER['product_id'].'&ref_module='.urlencode($APP_CONFIG['module']).'&ref_id='.$order_id.'&return='.$_GET['tab'].'">'.($dbGET_PRODUCT['icon'] ? show_icon($dbGET_PRODUCT['icon'], false, ['classes' => ['me-1']]) : false).$dbGET_PRODUCT['name'].'</a>' : h($dbGET_PRODUCT['icon'] ? show_icon($dbGET_PRODUCT['icon'], false, ['classes' => ['me-1']]) : false).$dbGET_PRODUCT['name'])?></td>
            </tr>
            <tr>
                <td>Status</td>
                <td><?=order_status_badge($dbGET_ORDER['status']).' '.ucwords($dbGET_ORDER['status'])?></td>
                <td class="d-none d-lg-table-cell">Owner</td>
                <td class="d-none d-lg-table-cell<?=(!$dbGET_PRODUCT['owner_id'] ? ' table-warning' : false)?>"><?=($dbGET_PRODUCT['owner_id'] ? show_user_avatar($dbGET_PRODUCT['owner_id']).'<span class="notranslate">'.$dbGET_PRODUCT['product_owner'].'</span>' : 'No Owner')?></td>
            </tr>
            <tr>
                <td>Required Date</td>
                <td class="<?=(in_array($dbGET_ORDER['status'], array('Pending', 'Late', 'Overdue')) ? ($dbGET_ORDER['status'] == 'Pending' ? ($dbGET_ORDER['dt_required'] <= (time() + 259200) ? 'warning' : false) : 'danger') : false)?>"><?=($dbGET_ORDER['dt_required'] ? date('l F j, Y', $dbGET_ORDER['dt_required']) : 'No Date Set')?></td>
                <td class="d-none d-lg-table-cell">Frequency</td>
                <td class="d-none d-lg-table-cell"><?=($dbGET_ORDER['workflow_id'] ? show_frequency_text(($dbGET_TEMPLATE['settings']['workflow']['custom_allowed'] && is_array($dbGET_WORKFLOW['workflow_data'])) ? $dbGET_WORKFLOW['workflow_data'] : $dbGET_TEMPLATE['settings']['workflow']) : 'One-Time Order')?></td>
            </tr>
            <?php if($dbGET_STORE_SETTINGS['approval_style'] == 'Per User'){ ?>
                <tr>
                    <td>Deadline</td>
                    <td colspan="3" class="<?=($dbGET_ORDER['status'] == 'Late' ? 'table-warning' : ($dbGET_ORDER['status'] == 'Overdue' ? 'table-danger' : false))?>"><?=($dbGET_ORDER['dt_deadline'] ? date('l F j, Y', $dbGET_ORDER['dt_deadline']) : 'No Deadline')?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <?php if(!$EDIT_MODE){ ?>
        <div class="mt-3 text-end">
            <?php
            if($ORDER_PERMISSIONS['cancel']){
                if($dbGET_ORDER['status'] == 'Pending' || $dbGET_ORDER['status'] == 'Late'){
                    echo create_button('Cancel Order', 'ghost-danger', 'action', ['onclick' => 'CancelOrder();']);
                }elseif($dbGET_ORDER['status'] == 'Cancelled'){
                    echo create_button('Restore Order', 'ghost-danger');
                }
            }

            if($dbGET_ORDER['dt_approved']){
                if(($dbGET_ORDER['is_mine'] || $dbGET_ORDER['can_approve'] || $ORDER_PERMISSIONS['ship']) && ($dbGET_ORDER['status'] == 'Pending' || $dbGET_ORDER['status'] == 'Late' || $dbGET_ORDER['status'] == 'Approved') && (!$dbGET_ORDER['dt_deadline'] || $dbGET_ORDER['dt_deadline'] > time() || $_SESSION['user']['permissions']['edit_overdue_orders'] == 'Y')){
                    echo create_button('Unapprove Order', 'ghost-danger');
                }

                if($ORDER_PERMISSIONS['ship'] && (!$incomplete_validations || $dbGET_ORDER['dt_cancelled'])){
                    if($dbGET_ORDER['status'] == 'Approved'){
                        echo create_button('Ship Order', 'primary ms-2');
                    }elseif($dbGET_ORDER['status'] == 'Shipped'){
                        echo create_button('Recall Shipment', 'ghost-danger');
                    }
                }
            }else{
                if(!$dbGET_ORDER['dt_cancelled']){
                    if($dbGET_ORDER['is_mine'] || $dbGET_ORDER['can_approve']){
                        if($ORDER_PERMISSIONS['ship'] && !$incomplete_validations){
                            echo create_button('Approve Order', 'warning ms-2', 'action', ['onclick' => 'app_form_submit($(this), \'#app-form\');']);
                            echo create_button('Approve & Ship', 'warning ms-2', 'action', ['onclick' => 'app_form_submit($(this), \'#app-form\');']);
                        }else{
                            echo create_button('Approve Order', 'warning ms-2', 'action', ['onclick' => 'app_form_submit($(this), \'#app-form\');']);
                        }
                    }elseif($ORDER_PERMISSIONS['edit']){
                        echo create_button('Force Approve', 'warning ms-2', 'action', ['onclick' => 'ApproveUnassignedOrder(this);']);
                    }
                }
            }
            ?>
        </div>
    <?php } ?>
</form>
