<div class="modal">
    <div class="modal-overlay"></div>
    <div class="modal-wrapper" style="max-width:575px;">
        <span><svg clip-rule="evenodd" fill="currentColor" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="m12 10.93 5.719-5.72c.146-.146.339-.219.531-.219.404 0 .75.324.75.749 0 .193-.073.385-.219.532l-5.72 5.719 5.719 5.719c.147.147.22.339.22.531 0 .427-.349.75-.75.75-.192 0-.385-.073-.531-.219l-5.719-5.719-5.719 5.719c-.146.146-.339.219-.531.219-.401 0-.75-.323-.75-.75 0-.192.073-.384.22-.531l5.719-5.719-5.72-5.719c-.146-.147-.219-.339-.219-.532 0-.425.346-.749.75-.749.192 0 .385.073.531.219z" />
            </svg></span>
        <div style="border-color: #437BFF; text-align: left;" class="appsumo__plg__userform-account-site-checkout appsumo__plg__userform-account-site-download-plugin appsumo__plg__form-alert">
            <p class="appsumo__plg__userform-account-site-checkout-text">
                We're really happy to have you on board. Thanks for believing in Wpmet. 
                <br>
                Your password has been sent to your email: <strong><?php echo $user->user_email; ?></strong>
            </p>
        </div>
        <form action="" method="post">
            <input type="hidden" value="<?php echo $nonce; ?>" name="appsumo__plg__save_userform_nonce">
            <div class="appsumo__plg__input-row">
                <div class="appsumo__plg__input-wrapper">
                    <label for="appsumo__plg__firstName">First Name <span>*</span></label>
                    <input id="appsumo__plg__firstName" class="appsumo__plg__input appsumo__plg__input-firstname" required type="text" placeholder="First Name" name="appsumo__plg__firstname" value="<?php echo $user->first_name; ?>">
                </div>
                <div class="appsumo__plg__input-wrapper">
                    <label for="appsumo__plg__lastName">Last Name <span>*</span></label>
                    <input id="appsumo__plg__lastName" class="appsumo__plg__input appsumo__plg__input-lastname" required type="text" placeholder="Last Name" name="appsumo__plg__lastname" value="<?php echo $user->last_name; ?>">
                </div>
            </div>

            <div style="text-align:left;" class="appsumo__plg__form-submit-btn">
                <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>

</div>

<script>
    jQuery('.modal-wrapper span svg').click(() => {
        jQuery('.modal').remove()
    })
</script>