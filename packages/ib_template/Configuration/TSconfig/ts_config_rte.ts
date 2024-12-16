
RTE.default.preset = ibCustom


# ------------------------------------------------------
# CONFIGURE RTE BLOCK FORMAT DROPDOWN
# ------------------------------------------------------
RTE.default.buttons.formatblock {
	items {
		#h2.label = Überschrift
		h3.label = h3 - Subline (Untertitel)
		#h4.label = Leitsatz
	}
	removeItems = pre, address, blockquote, div, aside, nav, header, footer, section, article, h1, h2,  h4, h5, h6
}

# ------------------------------------------------------
# SHOW / HIDE RTE Buttons
# ------------------------------------------------------
# see https://docs.typo3.org/typo3cms/extensions/rtehtmlarea/Configuration/PageTsconfig/interfaceConfiguration/Index.html
# blockstylelabel, blockstyle, textstylelabel, textstyle, fontstyle, fontsize, formatblock, bold, italic, emphasis, deletedtext, big,
# small, underline, strikethrough, subscript, superscript, lefttoright, righttoleft, left, center, right, justifyfull, orderedlist,
# unorderedlist, outdent, indent, textcolor, bgcolor, textindicator, emoticon, insertcharacter, line, link, image, table, user, acronym,
# findreplace, spellcheck, chMode, inserttag, removeformat, copy, cut, paste, undo, redo, showhelp, about, toggleborders,
# tableproperties, rowproperties, rowinsertabove, rowinsertunder, rowdelete, rowsplit, columninsertbefore, columninsertafter, columndelete,
# columnsplit, cellproperties, cellinsertbefore, cellinsertafter, celldelete, cellsplit, cellmerge
RTE.default {
	#showButtons = fontstyle,fontsize
	showButtons := addToList(rowsplit)
	# table
	hideButtons = outdent, indent, subscript, superscript, italic, insertcharacter, blockstyle, textstyle
}

# ------------------------------------------------------
# ???
# ------------------------------------------------------
RTE.default.proc {
	entryHTMLparser_db {
		tags {
			p.rmTagIfNoAttrib = 0
		}
	}
	#allowTags := p
	allowTags := addToList(p)
}

# ------------------------------------------------------
# add iframe for embed youtube videos
# see setup_tt_content_custom_layouts.ts for container wrapper
# ------------------------------------------------------
RTE.default.proc.allowTags := addToList(iframe,embed,object,param)
RTE.default.proc.entryHTMLparser_db.allowTags := addToList(iframe,embed,object,param)
RTE.default.proc.allowTagsOutside := addToList(iframe,embed,object,param)

RTE.default.contentCSS {
	file1 = typo3conf/ext/ib_template/Resources/Public/css/min/app.min.css
	file2 = typo3conf/ext/ib_template/Resources/Public/css/typo3_backend_rte.css
}

# Disable Upload and Drag&Drop tabs for images
RTE.default.buttons.image.options.removeItems = upload

# Disable Upload tab for media links
RTE.default.buttons.link.options.removeItems = media_upload

# ------------------------------------------------------
# format tables
# ------------------------------------------------------
RTE.default.proc.entryHTMLparser_db.tags.table {
	fixAttrib.style.unset = 1
	fixAttrib.width.unset = 1
	fixAttrib.height.unset = 1
}

RTE.default.proc.entryHTMLparser_db.tags.tr {
	fixAttrib.style.unset = 1
	fixAttrib.width.unset = 1
	fixAttrib.height.unset = 1
}

RTE.default.proc.entryHTMLparser_db.tags.td {
	fixAttrib.style.unset = 1
	fixAttrib.width.unset = 1
	fixAttrib.height.unset = 1
}

# configure imagesizes inside rte fields
RTE.default {
	buttons.image.options.magic.maxWidth = 1000
	#buttons.image.options.magic.maxHeight = 700
	buttons.image.options.plain.maxWidth = 1000
	#buttons.image.options.plain.maxHeight = 700
}

RTE.default.disableLayoutFieldsetInTableOperations = 1
#RTE.default.buttons.table.properties.headers.defaultValue = none

#/*
#	# Der RTE darf die Klasse "tableDefaultClass1" einer Tabelle zuordnen
#	# @see EXT:ib_template/Resources/Public/css/typo3_backend_rte.css
#	RTE.default.buttons.blockstyle.tags.table.allowedClasses := addToList(tableDefaultClass)
#	# Die Klasse "tableDefaultClass1" darf beim Speichern beibehalten werden
#	RTE.default.proc.allowedClasses := addToList(tableDefaultClass)
#	# Nur wenn Sie auch die Option innerhalb der Auswahlliste stylen wollen:
#	name = Default Table Style
#	RTE.classes.tableDefaultClass {
#		value = border: 3px solid green;
#	}
#
#	RTE.default.buttons.blockstyle.tags.table.allowedClasses := addToList(stack)
#	RTE.default.proc.allowedClasses := addToList(stack)
#*/
