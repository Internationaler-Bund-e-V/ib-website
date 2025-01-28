/**
 * track location/region data for reports
 */
(window as any).ibTrackLocation = () => {

    const locationData:any = document.querySelector('.ib-tracking-on-load');
    if (locationData && locationData.dataset.ibentity && locationData.dataset.ibentity == 'Location') {
        (window as any)._paq.push([
            'trackEvent', //push
            'Standort', // Category
            'Seitenaufruf', //Action
            locationData.dataset.locationnetwork + "  [" + locationData.dataset.locationnetworkid + "]", //Name Region + [ID]
            1 // value
        ]);
    }
};



