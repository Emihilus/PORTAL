#!/bin/bash
git add -A
git commit -m "MANUALCOMMIT: $(date +%d-%m-%Y_%H:%M:%S)"
git push -u origin master