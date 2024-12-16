Available Signals
=================

In case you need to adapt or extend the behaviour of solrfal the following signals exist you may consume in your slots.

DocumentFactory::fileMetaDataRetrieved
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Shortly after the (translated) MetaData record of a file is retrieved this Signal is emitted. The slot must take the IndexQueue\Item as first parameter and an ArrayObject as second parameter.
The ArrayObject contains the metadata you may modify in your slot.
The modified MetaData will then be hand over to the TypoScript "Service". As an result fields added in the slot to that Signal can be addressed from your regular TypoScript setup.
