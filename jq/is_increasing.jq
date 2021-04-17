def is_increasing: path(.[]) as $p|if $p[0]+1 < length then getpath([$p[0]+1])-getpath($p)<0 else true end]|all; [10,3,4,5,7]|is_increasing
