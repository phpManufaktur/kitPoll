//:interface to kitPoll
//:Please visit http://phpManufaktur.de for informations about kitForm!
/**
 * kitPoll
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id: kit_form.php 11 2011-04-13 15:58:46Z phpmanufaktur $
 */
if (file_exists(WB_PATH.'/modules/kit_poll/class.frontend.php')) {
	require_once(WB_PATH.'/modules/kit_poll/class.frontend.php');
	$poll = new pollFrontend();
	$params = $poll->getParams();
	$params[pollFrontend::param_preset] = (isset($preset)) ? (int) $preset : 1;
	$params[pollFrontend::param_css] = (isset($css) && (strtolower($css) == 'false')) ? false : true;
	$params[pollFrontend::param_name] = (isset($name)) ? strtolower($name) : ''; 
	$params[pollFrontend::param_chart] = (isset($chart)) ? strtolower($chart) : pollFrontend::chart_pie;
	$params[pollFrontend::param_chart_width] = (isset($chart_width)) ? (int) $chart_width :	300;
	$params[pollFrontend::param_chart_height] = (isset($chart_height)) ? (int) $chart_height : 300;
	$params[pollFrontend::param_chart_max_words] = (isset($chart_max_words)) ? (int) $chart_max_words : 5;
	$params[pollFrontend::param_chart_bkgnd_color_start] = (isset($chart_bkgnd_color_start)) ? strtolower($chart_bkgnd_color_start) : '#ffefd5';
	$params[pollFrontend::param_chart_bkgnd_color_end] = (isset($chart_bkgnd_color_end)) ? strtolower($chart_bkgnd_color_end) : '#ffe4b5';
	$params[pollFrontend::param_chart_border_color] = (isset($chart_border_color)) ? strtolower($chart_border_color) : '#ffdead';
	$params[pollFrontend::param_chart_legend_alpha] = (isset($chart_legend_alpha)) ? (int) $chart_legend_alpha : 40;
	if (!$poll->setParams($params)) return $poll->getError();
	return $poll->action();
}
else {
	return "kitPoll is not installed!";
}