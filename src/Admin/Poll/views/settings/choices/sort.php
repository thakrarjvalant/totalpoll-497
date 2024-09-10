<?php ! defined( 'ABSPATH' ) && exit(); ?><div class="totalpoll-settings-item">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Sort choices by', 'totalpoll' ); ?>
        </label>

        <p>
            <label>
                <input type="radio" name="" value="position" ng-model="editor.settings.choices.sort.field">
				<?php esc_html_e( 'Position', 'totalpoll' ); ?>
            </label>
            &nbsp;&nbsp;
            <label>
                <input type="radio" name="" value="votes" ng-model="editor.settings.choices.sort.field">
				<?php esc_html_e( 'Votes', 'totalpoll' ); ?>
            </label>
            &nbsp;&nbsp;
            <label>
                <input type="radio" name="" value="label" ng-model="editor.settings.choices.sort.field" >
				<?php esc_html_e( 'Label', 'totalpoll' ); ?>
                
            </label>
            &nbsp;&nbsp;
            <label>
                <input type="radio" name="" value="random" ng-model="editor.settings.choices.sort.field" >
				<?php esc_html_e( 'Random', 'totalpoll' ); ?>
                
            </label>
        </p>

    </div>
</div>
<div class="totalpoll-settings-item" ng-if="editor.settings.choices.sort.field !== 'random'">
    <div class="totalpoll-settings-field">
        <label class="totalpoll-settings-field-label">
			<?php esc_html_e( 'Direction', 'totalpoll' ); ?>
        </label>

        <p>
            <label>
                <input type="radio" name="" value="DESC" ng-model="editor.settings.choices.sort.direction">
				<?php esc_html_e( 'Descending', 'totalpoll' ); ?>
            </label>
            &nbsp;&nbsp;
            <label>
                <input type="radio" name="" value="ASC" ng-model="editor.settings.choices.sort.direction">
				<?php esc_html_e( 'Ascending', 'totalpoll' ); ?>
            </label>
        </p>
    </div>
</div>
