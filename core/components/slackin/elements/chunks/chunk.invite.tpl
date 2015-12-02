<div class="slackin-view">
    <div data-type="user" data-id="0">
        <h4>[[%slackin_invite_header]]</h4>

        <form class="slackin-invite form-inline" method="post" action="">
            <input type="hidden" name="propkey" value="[[+propkey]]"/>

            <div class="form-group">
                <input type="text" name="email" placeholder="[[%slackin_email]]" class="form-control"/>
                <input type="button" class="btn btn-primary" name="invite/send" data-confirm="false" data-type="error"
                       data-message="" value="[[%slackin_send]]"
                       title=""/>
            </div>

            [[+send:gt=`0`:then=`
            <p class="help-block">
                <small>[[%slackin_invite_send]]</small>
            </p>
            `:else=`
            <p class="help-block">
                <small>[[%slackin_invite_footer]]</small>
            </p>
            `]]

        </form>
    </div>
</div>
