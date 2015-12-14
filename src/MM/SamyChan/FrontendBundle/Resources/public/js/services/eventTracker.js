samyChanApp.factory('eventTracker', function() {

    return {
        track: function(eventCategory, eventAction, eventLabel, eventValue) {
            if (typeof ga == undefined) {
                return;
            }

            ga('send', 'event', eventCategory, eventAction, eventLabel, eventValue)
        },
    }
});