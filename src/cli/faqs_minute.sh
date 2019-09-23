#!/bin/bash
/data/sphinx/bin/indexer faqs_minute -c /data/sphinx/etc/sphinx.conf --rotate
/data/sphinx/bin/indexer --merge faqs faqs_minute --merge-dst-range state 2 2 --rotate