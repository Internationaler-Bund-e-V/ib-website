# Portale in TYPO3
In diesem Dokument werden die notwendigen Schritte dokumentiert, um ein Portal in TYPO3 neu anzulegen oder ein bestehendes zu übernehmen.
## Notwendige Informationen
## Schritte zur Übernahme eines Portals in TYPO3
1. Portal in TYPO3 anlegen (Seitenbaum, SiteConfig, constants_portals im Package ib_template anlegen)
2. Portal in Matomo anlegen und Matomo-ID im TYPO3 konfigurieren
3. SOLR-Einbindung konfigurieren
4. Domainübernahme und SSL-Zertifikat vorbereiten
5. DNS-Eintrag prüfen
6. HTTP-Basic-Auth entfernen
## Schritte zur Neuanlage eines Portals
### Site definition
- config/sites/<site_identifier>/config.yml
### Typoscript configuration

- packages/ib_template/Configuration/Typoscript/constants-portals/<site_identifier>.typoscript


#portal #typo3 #domain #siteconfig 