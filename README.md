# george

Add directory george in 1root/library/

Launch IRL => http://localhost/1root/library/George/

=====================================

In general.js of the landing when form is send after conversion_event(gtag_report_conversion); => [ADD]

let event = new CustomEvent('form-sended', {
bubbles: true,
cancelable: false
});

// Emit the event
document.dispatchEvent(event);

====================================

Add master-headerV4 in the same place of the another master-header
