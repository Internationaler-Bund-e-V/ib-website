# disable that to enable ts caching
#admPanel.override.tsdebug.forceTemplateParsing = 1

# disable 2fa (2 factor authentication)
setup.fields.mfaProviders.disabled = 1

# user TT_NEWS config
TCAdefaults.tt_news {
	hidden = 0
	author = full name
	author_email = name@domain.tld
}

# -----------------------------------------------------------------------------
# CACHE
# -----------------------------------------------------------------------------
options.clearCache.pages = 1
options.clearCache.all = 1

# -----------------------------------------------------------------------------
# workspace preview link (hours)
# -----------------------------------------------------------------------------
options.workspaces.previewLinkTTLHours = 168

# -----------------------------------------------------------------------------
# RTE Height
# -----------------------------------------------------------------------------
options.RTESmallWidth = 600
options.RTESmallHeight = 300

# -----------------------------------------------------------------------------
# disable direct file upload
# -----------------------------------------------------------------------------
setup.override.edit_docModuleUpload = 0
