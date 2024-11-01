<?php

if (!defined('ABSPATH')) {
    exit;
}

class StepQueue_Uninstall_feedback_Listener {
    protected $data;
    function __construct($var) {
        $this->data = $var;
        add_action( 'admin_footer', array( $this, 'deactivate_scripts' ) );
        add_action('wp_ajax_stepqueue_uninstall_feedback', array($this, "stepqueue_uninstall_feedback"));
    }
    
    private function get_uninstall_reasons() {
        $reasons = array(
            array(
                'id'          => 'could-not-understand',
                'text'        => __('I couldn\'t understand how to make it work',$this->data['lang']),
                'type'        => 'textarea',
                'placeholder' => __('Would you like us to assist you?',$this->data['lang'])
            ),
            array(
                'id'          => 'found-better-plugin',
                'text'        => __('I found a better plugin',$this->data['lang']),
                'type'        => 'text',
                'placeholder' => __('Which plugin?',$this->data['lang'])
            ),
            array(
                'id'          => 'not-have-that-feature',
                'text'        => __('The plugin is great, but I need specific feature that you don\'t support',$this->data['lang']),
                'type'        => 'textarea',
                'placeholder' => __('Could you tell us more about that feature?',$this->data['lang'])
            ),
            array(
                'id'          => 'is-not-working',
                'text'        => __('The plugin is not working',$this->data['lang']),
                'type'        => 'textarea',
                'placeholder' => __('Could you tell us a bit more whats not working?',$this->data['lang'])
            ),
            array(
                'id'          => 'looking-for-other',
                'text'        => __('It\'s not what I was looking for',$this->data['lang']),
                'type'        => '',
                'placeholder' => ''
            ),
            array(
                'id'          => 'did-not-work-as-expected',
                'text'        => __('The plugin didn\'t work as expected',$this->data['lang']),
                'type'        => 'textarea',
                'placeholder' => __('What did you expect?',$this->data['lang'])
            ),
            array(
                'id'          => 'other',
                'text'        => __('Other',$this->data['lang']),
                'type'        => 'textarea',
                'placeholder' => __('Could you tell us a bit more?',$this->data['lang'])
            ),
        );

        return $reasons;
    }
    
    public function deactivate_scripts() {
        global $pagenow;

        if ( 'plugins.php' != $pagenow ) {
            return;
        }

        $reasons = $this->get_uninstall_reasons();
        ?>

        <div class="<?php echo $this->data['slug']; ?>-modal" id="<?php echo $this->data['slug']; ?>-<?php echo $this->data['slug']; ?>-modal">
            <div class="<?php echo $this->data['slug']; ?>-modal-wrap">
                <div class="<?php echo $this->data['slug']; ?>-modal-header">
                    <div style="text-align: center;"><img src="<?php echo SQ_WEI_INV_IMG.'logo.png';?>"><h3 style="margin-top: 5px;"><?php echo $this->data['name']; ?></h3></div>
                </div>
                <div class="<?php echo $this->data['slug']; ?>-modal-body">
                    <h3><?php _e( 'If you have a moment, please let us know why you are deactivating:', $this->data['lang'] ); ?></h3>
                    <ul class="reasons">
                        <?php foreach ($reasons as $reason) { ?>
                            <li data-type="<?php echo esc_attr( $reason['type'] ); ?>" data-placeholder="<?php echo esc_attr( $reason['placeholder'] ); ?>">
                                <label><input type="radio" name="selected-reason" value="<?php echo $reason['id']; ?>"> <?php echo $reason['text']; ?></label>
                            </li>
                        <?php } ?>
                    </ul>
                    <label style="color:red;">
                        <input type="checkbox" checked id="send_diagonastics_information">Send diagnostics informations to developers for analyzing the issue.
                    </label>
                </div>

                <div class="<?php echo $this->data['slug']; ?>-modal-footer">
                    <a href="#" class="button-primary dont-bother-me"><?php _e( 'I rather wouldn\'t say', $this->data['lang'] ); ?></a>
                    <button class="button-primary <?php echo $this->data['slug']; ?>-model-submit"><?php _e( 'Submit & Deactivate', $this->data['lang'] ); ?></button>
                    <button class="button-secondary <?php echo $this->data['slug']; ?>-model-cancel"><?php _e( 'Cancel', $this->data['lang'] ); ?></button>
                </div>
            </div>
        </div>

        <style type="text/css">
            .<?php echo $this->data['slug']; ?>-modal {
                position: fixed;
                z-index: 99999;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                background: rgba(0,0,0,0.5);
                display: none;
            }

            .<?php echo $this->data['slug']; ?>-modal.modal-active {
                display: block;
            }

            .<?php echo $this->data['slug']; ?>-modal-wrap {
                width: 50%;
                position: relative;
                margin: 5% auto;
                background: #fff;
            }

            .<?php echo $this->data['slug']; ?>-modal-header {
                border-bottom: 1px solid #eee;
                padding: 8px 20px;
            }

            .<?php echo $this->data['slug']; ?>-modal-header h3 {
                line-height: 150%;
                margin: 0;
            }

            .<?php echo $this->data['slug']; ?>-modal-body {
                padding: 5px 20px 20px 20px;
            }
            .<?php echo $this->data['slug']; ?>-modal-body .input-text,.<?php echo $this->data['slug']; ?>-modal-body textarea {
                width:75%;
            }
            .<?php echo $this->data['slug']; ?>-modal-body .reason-input {
                margin-top: 5px;
                margin-left: 20px;
            }
            .<?php echo $this->data['slug']; ?>-modal-footer {
                border-top: 1px solid #eee;
                padding: 12px 20px;
                text-align: right;
            }
        </style>

        <script type="text/javascript">
            (function($) {
                $(function() {
                    var modal = $( '#<?php echo $this->data['slug']; ?>-<?php echo $this->data['slug']; ?>-modal' );
                    var deactivateLink = '';

                    $( '#the-list' ).on('click', 'a.<?php echo $this->data['slug']; ?>-deactivate-link', function(e) {
                        e.preventDefault();

                        modal.addClass('modal-active');
                        deactivateLink = $(this).attr('href');
                        modal.find('a.dont-bother-me').attr('href', deactivateLink).css('float', 'left');
                    });

                    modal.on('click', 'button.<?php echo $this->data['slug']; ?>-model-cancel', function(e) {
                        e.preventDefault();

                        modal.removeClass('modal-active');
                    });

                    modal.on('click', 'input[type="radio"]', function () {
                        var parent = $(this).parents('li:first');

                        modal.find('.reason-input').remove();

                        var inputType = parent.data('type'),
                            inputPlaceholder = parent.data('placeholder'),
                            reasonInputHtml = '<div class="reason-input">' + ( ( 'text' === inputType ) ? '<input type="text" class="input-text" size="40" />' : '<textarea rows="5" cols="45"></textarea>' ) + '</div>';

                        if ( inputType !== '' ) {
                            parent.append( $(reasonInputHtml) );
                            parent.find('input, textarea').attr('placeholder', inputPlaceholder).focus();
                        }
                    });

                    modal.on('click', 'button.<?php echo $this->data['slug']; ?>-model-submit', function(e) {
                        e.preventDefault();

                        var button = $(this);

                        if ( button.hasClass('disabled') ) {
                            return;
                        }

                        var $radio = $( 'input[type="radio"]:checked', modal );

                        var $selected_reason = $radio.parents('li:first'),
                            $input = $selected_reason.find('textarea, input[type="text"]');
                        var $diagnostics = '';
                        if($('#send_diagonastics_information').is(':checked'))
                        {
                            $diagnostics = 'send';
                        }
                        else
                        {
                            $diagnostics = 'avoid';
                        }
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'stepqueue_uninstall_feedback',
                                plugin: '<?php echo $this->data['name']; ?>',
                                version: '<?php echo $this->data['version']; ?>',
                                diagnostics : $diagnostics,
                                reason_id: ( 0 === $radio.length ) ? 'none' : $radio.val(),
                                reason_info: ( 0 !== $input.length ) ? $input.val().trim() : ''
                            },
                            beforeSend: function() {
                                button.addClass('disabled');
                                button.text('Processing...');
                            },
                            complete: function(data) {
                                window.location.href = deactivateLink;
                            }
                        });
                    });
                });
            }(jQuery));
        </script>

        <?php
    }
    static function stepqueue_uninstall_feedback() {
        global $wpdb;

        if ( ! isset( $_POST['reason_id'] ) ) {
            wp_send_json_error();
        }

        $current_user = wp_get_current_user();

        $data = array(
            'reason_id'     => sanitize_text_field( $_POST['reason_id'] ),
            'plugin'        => sanitize_text_field($_POST['plugin']),
            'auth'          => 'stepqueue_uninstall_1234#',
            'date'          => current_time('mysql'),
            'url'           => home_url(),
            'user_email'    => $current_user->user_email,
            'reason_info'   => isset( $_REQUEST['reason_info'] ) ? trim( stripslashes( $_REQUEST['reason_info'] ) ) : '',
            'plugin_version'=> sanitize_text_field($_POST['version'])
        );
        if($_POST['diagnostics'] == 'send')
        {
            $data['software']       = $_SERVER['SERVER_SOFTWARE'];
            $data['php_version']    = phpversion();
            $data['mysql_version']  = $wpdb->db_version();
            $data['wp_version']     = get_bloginfo( 'version' );
            $data['locale']         = get_locale();
            $data['multisite']      = is_multisite() ? 'Yes' : 'No';
        }
        else
        {
            $data['software']       = '';
            $data['php_version']    = '';
            $data['mysql_version']  = '';
            $data['wp_version']     = '';
            $data['locale']         = '';
            $data['multisite']      = '';
        }
        $resp = wp_remote_post('https://stepqueue.com/wp-json/stepqueue/v1/uninstall', array(
                'method'      => 'POST',
                'timeout'     => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking'    => false,
                'headers'     => array( 'user-agent' => 'stepqueue/' . md5( esc_url( home_url() ) ) . ';' ),
                'body'        => $data,
                'cookies'     => array()
            )
        );
        wp_send_json_success();
    }
}