<?php 
define('ASR_ACTION_MODE', (isset($_GET['upgrade']) && $_GET['upgrade'] == 'true' ? 'upgrade' : 'create'));
define('ASR_DOWNLOAD_ID', (strlen(get_query_var('asr_download_id')) < 10 && get_query_var('asr_download_id') != '' ? get_query_var('asr_download_id') : 9));

define('ASR_CODE', (function(){
    if(ASR_ACTION_MODE == 'upgrade'){ 
        return '';
    }
    if(strlen(get_query_var('asr_download_id')) >= 10){
        return get_query_var('asr_download_id');
    }
    return get_query_var('redeem_code');
    })()
);

if(\Appsumo_Redeem::campaign_data(ASR_DOWNLOAD_ID) == null){
    echo 'Error code: 1002; Lost dattebayo! Please Contact support.';
    exit;
}

$user = (object)[
    'user_email' => '',
    'display_name' => '',
    'ID' => 0,
];

$hide_fields = [];
if(ASR_ACTION_MODE == 'upgrade'){
    $hide_fields[] = 'name';

    $cuser = wp_get_current_user();
    $user->ID = $cuser->ID;
    $user->display_name = $cuser->display_name;
    $user->user_email = $cuser->user_email;

    if((0 != $user->ID)){
        $hide_fields[] = 'password';
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?php the_title(); ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo \Appsumo_Redeem::asset_url(); ?>css/appsumo-redeem.css?version=<?php echo \Appsumo_Redeem::version(); ?>" rel="stylesheet"></head>
<body>
    <noscript>
        <strong>We're sorry but redeem form doesn't work properly without JavaScript enabled. Please enable JavaScript to continue.</strong>
    </noscript>
    <?php //print_r(wp_get_current_user()); ?>
    <div id="appsumo-redeem"></div>

    <script>
        const BASE_URL = '<?php echo get_site_url(); ?>/';

        var appsumo_campaign_data = JSON.parse('<?php echo json_encode(\Appsumo_Redeem::campaign_data(ASR_DOWNLOAD_ID)); ?>');

        var appsumo_redeem_config = {

            assets_path: '<?php echo \Appsumo_Redeem::asset_url(); ?>',

            download_id: '<?php echo ASR_DOWNLOAD_ID; ?>',

            field_data: {
                code: '<?php echo ASR_CODE; ?>',
                nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
                email: '<?php echo $user->user_email; ?>',
                password: '',
                name: '<?php echo $user->display_name; ?>',
            },

            form_config: {
                button_text: '<?php echo (ASR_ACTION_MODE == 'create') 
                    ? \Appsumo_Redeem::campaign_data(ASR_DOWNLOAD_ID)->action_button_text[0] 
                    : \Appsumo_Redeem::campaign_data(ASR_DOWNLOAD_ID)->action_button_text[1]; ?>',
                hide_fields: <?php echo json_encode($hide_fields); ?>,
                logout_url: '<?php echo (ASR_ACTION_MODE == 'create') ? "#" : wp_logout_url( get_permalink() . '/?upgrade=true' ); ?>',
                logout_text: '<?php echo (0 == $user->ID || ASR_ACTION_MODE == 'create') ? "" : 'Not you? Use another email.'; ?>',
                validate_url: BASE_URL + 'wp-json/appsumo-redeem/v1/redeem/validate_<?php echo ASR_ACTION_MODE; ?>/', // on validate
                submit_url: BASE_URL + 'wp-json/appsumo-redeem/v1/redeem/<?php echo ASR_ACTION_MODE; ?>/', // on submit
            }
        }
    </script>

    <!-- built files will be auto injected -->
<script type="text/javascript" src="<?php echo \Appsumo_Redeem::asset_url(); ?>js/appsumo-redeem.js?version=<?php echo \Appsumo_Redeem::version(); ?>"></script>
</body>
</html>