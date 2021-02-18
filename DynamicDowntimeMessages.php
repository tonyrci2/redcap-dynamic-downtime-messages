<?php
/**
 * External Module: Dynamic Downtime Messages
 * Display a downtime banner (alerting users when the system is going down) that changes as downtime period approaches.
 * @author Tony Jin, Stony Brook Medicine / University
 */
namespace StonyBrookMedicine\DynamicDowntimeMessages;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;
use Logging;
use REDCap;
require __DIR__ . '/vendor/autoload.php';

// Future Directions:

// Customize time zone – picklist or manual entry. - DONE 12/2/19
// Do not display banner or send emails if past end date - DONE 12/2/19
// Emails - In spirit of one-off solution – include cron jobs that deliver emails to recent users.
// One email a week out
// One email 1 day prior
// Custom messages.
// Customized time syntax based on user input
// Embed placeholders in custom message for formatted time, relative time from now, etc.
// Make banner dismissible?
// Configure display interval (e.g., show on days 7-5, and then 2-0).


class DynamicDowntimeMessages extends AbstractExternalModule {	
	function redcap_every_page_top() {
		// $institution = $GLOBALS['institution'];
		$institution = !(empty($this->getSystemSetting('inst-display-name'))) 
						? $this->getSystemSetting('inst-display-name') 
						: (
						(isset($GLOBALS['institution'])) ? $GLOBALS['institution'] : ''
						);
		$institution = $this->getSystemSetting('inst-display-name') . 
		$institutionText = (!empty($institution)) ? ' @ '.$institution : '';
		// print_r($GLOBALS);
		// print_r(get_defined_constants());
		if (PAGE === 'index.php' || PAGE === 'redcap/index.php' || PAGE === 'index.php?action=myprojects' || PAGE === 'redcap/index.php?action=myprojects') {
		$timezone=strip_tags($this->getSystemSetting('timezone'));
		// *Always* use a timezone identifier like "Europe/London" and not an abbreviation such as "GMT", since the latter will not account for daylight savings time, whereas the former will.
		// https://stackoverflow.com/questions/17694894/different-timezone-types-on-datetime-object
		// What's nice about using the tz identifier is that, when printing a date/time, depending on the date, the correct timezone abbreviation (EST vs EDT) is automatically outputted.
		\Moment\Moment::setLocale('en_US');
		\Moment\Moment::setDefaultTimezone($timezone);
		$startTime=$this->getSystemSetting('datetime-down');
		$downTimeMinutes=(int) $this->getSystemSetting('duration-down');
		$m = new \Moment\Moment($startTime,$timezone);
		// echo $m->format() . '<br>';
		$e = $m->cloning()->addMinutes($downTimeMinutes);

		if ($downTimeMinutes < 60 && $downTimeMinutes > 0) {
			$downTimeHuman = round($downTimeMinutes,0) . "&nbsp;minutes.";
		}
		elseif (round($downTimeMinutes/60,2,PHP_ROUND_HALF_DOWN) == 1.0) {
			$downTimeHuman = 'one&nbsp;hour';
		}
		else {
			$downTimeHuman = round($downTimeMinutes/60/.25,0,PHP_ROUND_HALF_DOWN)*.25 . "&nbsp;hours";
		}


		// echo $e->format(); 
		// echo '<br>';

		// echo $e->getHour() . '<br>';
		// echo $e->getMinute() . '<br>';
		// echo $e->format('g:i A') . '<br>';

		$fullDate = $m->format('l, F jS, Y');
		$startTime = $m->format('g:i');
		$endTime = $e->format('g:i A T');
		// echo $fullDate . '<br>';
		// echo $startTime . '<br>';
		// echo $endTime. '<br>';

		// echo '<br>';
		$momentFromVo = $m->fromNow();

		$relative = $momentFromVo->getRelative();
		// echo $relative;
		// echo "<br>";

		$relCal =  $m->calendar();
		// echo $relCal;
		// echo "<br>";

		// echo "Due to scheduled maintenance, REDCap @ Stony Brook Medicine will be down (${relative}) on ${fullDate} to ${endTime}.";
		// echo '<br>';
		// echo "Due to scheduled maintenance, REDCap @ Stony Brook Medicine will be down (${relCal}) on ${fullDate}.";

		// Will only display if downtime comes after the present time.

		if ($momentFromVo->getHours() < 0) {
			$homepageNotice= '<div class="' . $this->getSystemSetting('div-class') . '" style="text-align:center">'.'<u><b><span style="font-size:120%">Notice of Planned Outage</span></b></u><br><br>Due to ' . $this->getSystemSetting('reason-down') . ', REDCap' . $institutionText . ' will be down briefly, &nbsp;<span style="font-weight:bold;font-size:110%;">'."*{$relative}*</span>,&nbsp;on<br><br><h6><b>{$fullDate}</b>,</h6>".'<span style="font-weight:bold;font-size:115%">'." {$startTime} - {$endTime}.</span>".'<p style="text-align:center;padding-top:6px"></b>Thank you for your patience as we work to keep REDCap up to date and running smoothly.</p></div>';
			?>
            <script type='text/javascript'>
                $(document).ready(function() {
                	if ($("div[style|='margin-bottom:10px;']").length >= 1) {$("div[style|='margin-bottom:10px;']")[0].innerHTML='<?php echo $homepageNotice; ?>'}
 					else {$("nav[role|='navigation']").after('<div style="margin-bottom:10px;"><?php echo $homepageNotice; ?></div>')}; 
                });
                //*[@id="pagecontent"]/div[1]/div
            </script>
            <?php

		}
		}




		// $sql = "UPDATE redcap_config SET `value` = {$homepageNotice} WHERE `field_name` = 'homepage_announcement'";
	}
}
