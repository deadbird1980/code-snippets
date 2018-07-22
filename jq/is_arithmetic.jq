def is_arithmetic: (.[1]-.[0]) as $delta|[path(.[]) as $p|if $p[0]+1 < length then getpath([$p[0]+1])-getpath($p)==$delta else true end]|all; [1,3,4,5,7]|is_arithmetic
