#!/bin/sh

nohup php publishAllArticle.php h5 "select aid from article where status=2 order by aid desc" &
