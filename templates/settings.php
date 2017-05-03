<div id="ownerfixer" class="section">
	<fieldset class="personalblock">
	    <h2><?php p($l->t('Owner Fixer')); ?></h2>
        <em><?php p($l->t('To enable owner fixer you should supply quota service')); ?></em>
        <br>
        <label><?php p($l->t('File permission umask')); ?></label>
        <br>
        <input class="permission" id="of-permission" value="<?php echo \OC::$server->getConfig()->getAppValue('owner_fixer', 'permission_umask'); ?>" placeholder="Umask" type="text">
        <br>
        <label><?php p($l->t('Quota Service URI (Owner Fixer send http get request to this URI and expects json string)')); ?></label>
        <br>
        <em><?php p($l->t('Sample Response: {"quota_limit":10000,"current_usage":5000}')); ?></em>
        <br>
        <input class="quota_uri" id="of-quota_uri" value="<?php echo \OC::$server->getConfig()->getAppValue('owner_fixer', 'quota_service_uri'); ?>" placeholder="Quota service URI" type="text" style="width: 300px">
        <br>
        <div class="actions">
            <button class="save"><?php p($l->t('Save'));?></button>
            <span id="of-msg" class="msg"></span>
        </div>
    </fieldset>
</div>