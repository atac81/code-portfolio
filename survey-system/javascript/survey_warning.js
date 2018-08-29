/*
 * Warning message generated before user navigates away from page (i.e. back button, window close, new URL in address bar, etc.)
 */

window.onbeforeunload = function (evt) {
	if (document.survey.SubmitSurvey.disabled != true) {
		var message = 'You have not yet submitted your survey responses.\nBy leaving this page prior to submitting, you will lose all of your responses, and you will have to start all over again.';
		if (typeof evt == 'undefined') {
			evt = window.event;
		}
		if (evt) {
			evt.returnValue = message;
		}
		return message;
	}
}
