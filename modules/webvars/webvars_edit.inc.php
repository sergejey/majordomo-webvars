<?php
if (defined('SETTINGS_CODEEDITOR_TURNONSETTINGS')) {
    $out['SETTINGS_CODEEDITOR_TURNONSETTINGS'] = SETTINGS_CODEEDITOR_TURNONSETTINGS;
    $out['SETTINGS_CODEEDITOR_UPTOLINE'] = SETTINGS_CODEEDITOR_UPTOLINE;
    $out['SETTINGS_CODEEDITOR_SHOWERROR'] = SETTINGS_CODEEDITOR_SHOWERROR;
}

if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$table_name = 'webvars';
$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
if ($this->mode == 'update') {
    $ok = 1;
    //updating 'HOSTNAME' (varchar)
    $hostname = gr('hostname');
    $rec['HOSTNAME'] = $hostname;

    if (!preg_match('/^http:/is', $rec['HOSTNAME']) && !preg_match('/^https:/is', $rec['HOSTNAME']) && !preg_match('/\%/is', $rec['HOSTNAME'])) {
        $out['ERR_HOSTNAME'] = 1;
        $ok = 0;
    }

    $title = gr('title');
    $rec['TITLE'] = $title;

    $search_pattern = gr('search_pattern');
    $rec['SEARCH_PATTERN'] = trim($search_pattern);

    $check_pattern = gr('check_pattern');
    $rec['CHECK_PATTERN'] = trim($check_pattern);

    $delhtml = gr('delhtml','int');
    $rec['DELHTML'] = $delhtml;

    $encoding = gr('encoding');
    $rec['ENCODING'] = trim($encoding);

    $auth = gr('auth');
    $rec['AUTH'] = (int)$auth;

    $username = gr('username');
    $rec['USERNAME'] = $username;

    $password = gr('password');
    $rec['PASSWORD'] = $password;

    $linked_object = gr('linked_object');
    $rec['LINKED_OBJECT'] = trim($linked_object);

    $linked_property = gr('linked_property');
    $rec['LINKED_PROPERTY'] = trim($linked_property);

    $code = gr('code');
    $rec['CODE'] = $code;

    $run_type = gr('run_type');

    if ($run_type == 'script') {
        $script_id = gr('script_id', 'int');
        $rec['SCRIPT_ID'] = $script_id;
    } else {
        $rec['SCRIPT_ID'] = 0;
    }

    if ($rec['CODE'] != '' && $run_type == 'code') {
        $errors = php_syntax_error($code);
        if ($errors) {
            $out['ERR_LINE'] = preg_replace('/[^0-9]/', '', substr(stristr($errors, 'php on line '), 0, 18)) - 2;
            $out['ERR_CODE'] = 1;
            $errorStr = explode('Parse error: ', htmlspecialchars(strip_tags(nl2br($errors))));
            $errorStr = explode('Errors parsing', $errorStr[1]);
            $errorStr = explode(' in ', $errorStr[0]);
            //var_dump($errorStr);
            $out['ERRORS'] = $errorStr[0];
            $out['ERR_FULL'] = $errorStr[0] . ' ' . $errorStr[1];
            $out['ERR_OLD_CODE'] = $old_code;
            $ok = 0;
        }
    }

    $online_interval = gr('online_interval','int');
    $rec['ONLINE_INTERVAL'] = $online_interval;

    if ($ok) {
        $rec['LATEST_VALUE'] = '';
        $rec['CHECK_LATEST'] = date('Y-m-d H:i:s');
        $rec['CHECK_NEXT'] = date('Y-m-d H:i:s');
        if ($rec['ID']) {
            SQLUpdate($table_name, $rec); // update
        } else {
            $new_rec = 1;
            $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
        }
        $out['OK'] = 1;
    } else {
        $out['ERR'] = 1;
    }
}
//options for 'HOST TYPE' (select)
$tmp = explode('|', DEF_TYPE_OPTIONS);
foreach ($tmp as $v) {
    if (preg_match('/(.+)=(.+)/', $v, $matches)) {
        $value = $matches[1];
        $title = $matches[2];
    } else {
        $value = $v;
        $title = $v;
    }
    $out['TYPE_OPTIONS'][] = array('VALUE' => $value, 'TITLE' => $title);
    $type_opt[$value] = $title;
}

$optionsTypeCnt = count($out['TYPE_OPTIONS']);
for ($i = 0; $i < $optionsTypeCnt; $i++) {
    if ($out['TYPE_OPTIONS'][$i]['VALUE'] == $rec['TYPE'])
        $out['TYPE_OPTIONS'][$i]['SELECTED'] = 1;
}

if (is_array($rec)) {
    foreach ($rec as $k => $v) {
        if (!is_array($v)) {
            $rec[$k] = htmlspecialchars($v);
        }
    }
}
outHash($rec, $out);
$out['LOG'] = nl2br($out['LOG']);

$out['SCRIPTS'] = SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");

