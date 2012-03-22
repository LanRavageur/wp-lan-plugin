
var LanOrg = LanOrg || {};

LanOrg.installAjaxEvents = function () {
  jQuery('DIV#content a.lanorg-link').click(LanOrg.handleLinkEvent);
  jQuery('FORM.lanorg-form').ajaxForm({
    semantic: true,
    success: LanOrg.handleFormResponse,
    beforeSubmit: LanOrg.handleFormSubmit,
    data: { lanorg_ajax: true }
  });
};

LanOrg.initHistoryState = function () {
  History.Adapter.bind(window, 'statechange', LanOrg.handleStateChange);
};

LanOrg.navigateTo = function (url) {
  LanOrg.handlePageLoad();
  jQuery.get(url, {'lanorg_ajax': 1}, LanOrg.setPageContent);
};

LanOrg.handlePageLoad = function () {
  jQuery('DIV#content > DIV.lanorg-2col-right').fadeTo(0, 0.5);
};

LanOrg.setPageContent = function (content) {
  jQuery('DIV#content').html(content);
  LanOrg.installAjaxEvents();
};

LanOrg.handleStateChange = function () {
  var state = History.getState();
  LanOrg.navigateTo(state.url);
};

LanOrg.handleLinkEvent = function () {
  var href = jQuery(this).attr('href');
  History.pushState(null, null, href);
  return false;
};

LanOrg.handleFormSubmit = function (arr, form, options) {
  var action = form.attr('action');
  if (typeof action === 'undefined' || action === false) {
    var state = History.getState();
    options.url = state.url;
  }
  LanOrg.handlePageLoad();
  return true;
};

LanOrg.handleFormResponse = function (response, data) {
  LanOrg.setPageContent(response);
};

LanOrg.installAjaxEvents();
LanOrg.initHistoryState();
