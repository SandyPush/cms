#!/bin/sh

nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 000001 and 050000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 050001 and 100000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 100001 and 150000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 150001 and 200000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 200001 and 250000 order by aid desc" &

nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 250001 and 300000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 300001 and 350000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 350001 and 400000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 400001 and 450000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 450001 and 500000 order by aid desc" &

nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 500001 and 550000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 550001 and 600000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 600001 and 650000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 650001 and 700000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 700001 and 750000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 750001 and 800000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 800001 and 850000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid between 850001 and 900000 order by aid desc" &
nohup php publishAllArticle.php vodone "select aid from article where status=2 and aid >= 900001 order by aid desc" &
