<form id="latexpad" action="#" method="post">
    <fieldset class="personalblock">
        <strong><?php echo $l->t('Collaborative Latex Documents');?></strong>
        <p>
            <label for="etherpad_apikey"><?php echo $l->t('Etherpad APIKEY');?></label>
            <input type="text" id="etherpad_apikey" name="etherpad_apikey"
                value="<?php echo $_['etherpad_apikey']; ?>" />
                <em><?php echo $l->t('You can find it in APIKEY.txt file in the root folder of Etherpad Lite'); ?></em>
        </p>
        <p>
            <label for="etherpad_server"><?php echo $l->t('Etherpad Server');?></label>
            <input type="text" id="etherpad_server" name="etherpad_server"
                value="<?php echo $_['etherpad_server']; ?>" />
                <em><?php echo $l->t('ex:'); ?> http://gorillas.com.br:9001</em>
        </p>
        <input type="submit" value="<?php echo $l->t('Save');?>" />
    </fieldset>
</form>
