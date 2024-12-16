/**
 * track location/region data for reports
 */
function ibTrackLocation() {
    
    var locationData = $(".ib-tracking-on-load");
    if (locationData.data('ibentity') == 'Location') {
    

        var matomoData = [
            'trackEvent', //push
            'Standort', // Category
            'Seitenaufruf', //Action
            locationData.data('locationnetwork') + "  [" + locationData.data('locationnetworkid') + "]", //Name Region + [ID]
            1 // value
        ]

        
        _paq.push(matomoData);
    }
}

