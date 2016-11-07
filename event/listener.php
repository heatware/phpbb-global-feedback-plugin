<?php
namespace heatware\integration\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class listener implements EventSubscriberInterface
{
    static public function getSubscribedEvents()
    {
        return array(
            'core.viewtopic_cache_user_data'    => 'viewtopic_cache_heatware_to_user_data',
            'core.viewtopic_modify_post_row'    => 'viewtopic_add_heatware_to_post_row',
        );
    }

    public function viewtopic_cache_heatware_to_user_data($event)
    {
        // Make sure to cache all of the values we need when modifying the post row
        $user_cache_data = $event['user_cache_data'];
        $user_cache_data['heatware_enabled'] = $event['row']['heatware_enabled'];
        $user_cache_data['heatware_id'] = $event['row']['heatware_id'];
        $user_cache_data['heatware_suspended'] = $event['row']['heatware_suspended'];
        $user_cache_data['heatware_positive'] = $event['row']['heatware_positive'];
        $user_cache_data['heatware_negative'] = $event['row']['heatware_negative'];
        $user_cache_data['heatware_neutral'] = $event['row']['heatware_neutral'];
        $event['user_cache_data'] = $user_cache_data;
    }

    public function viewtopic_add_heatware_to_post_row($event)
    {
        global $config, $user;
        
        if ($event['row']['user_id'] != ANONYMOUS)
        {
            $user->add_lang_ext('heatware/integration', 'common');

            $post_row = $event['post_row'];
            $post_row['HEATWARE_FEEDBACK_PREFIX'] = $user->lang('HEATWARE_FEEDBACK_PREFIX');

            if ( $event['user_poster_data']['heatware_id'] > 0 && ($config['heatware_global_enable'] || $event['user_poster_data']['heatware_enabled']) )
            {
                $post_row['HEATWARE_ID'] = $event['user_poster_data']['heatware_id'];
                if( $event['user_poster_data']['heatware_suspended'] == '1' )
                {
                    $feedback .= $user->lang('HEATWARE_SUSPENDED');
                }
                else
                {
                    $feedback .= $event['user_poster_data']['heatware_positive'] . '-' .
                                $event['user_poster_data']['heatware_negative'] . '-' .
                                $event['user_poster_data']['heatware_neutral'];
                }
            }
            else
            {
                $feedback .= $user->lang('HEATWARE_NOT_AVAILABLE');
            }

            $post_row['HEATWARE_FEEDBACK'] = $feedback;
            $event['post_row'] = $post_row;
        }
    }
}