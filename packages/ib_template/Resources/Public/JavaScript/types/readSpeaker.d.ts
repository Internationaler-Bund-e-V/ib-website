type ReadspeakerConfig = {
    general: {
        usePost: boolean,
        cookieLifetime: number
    },
    ui: {
        disableDetachment: boolean,
        disableDownload: boolean,
        playbackDetach: boolean
    },
    settings: {
        kb: {
            clicklisten: string,
            controlpanel: string,
            dictionary: string,
            download: string,
            enlarge: string,
            fontsizeminus: string,
            fontsizeplus: string,
            formreading: string,
            help: string,
            menu: string,
            pagemask: string,
            pause: string,
            play: string,
            playerfocus: string,
            settings: string,
            stop: string,
            textmode: string,
            translation: string,
            readingvoice: string,
            detachfocus: string
        }
    }
}

declare global {
    interface Window { 
        rsConf: ReadspeakerConfig
    }
}
