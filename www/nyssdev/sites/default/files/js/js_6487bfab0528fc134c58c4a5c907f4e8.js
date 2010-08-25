// $Id: jquery.js,v 1.12.2.3 2008/06/25 09:38:39 goba Exp $ 

/*
 * jQuery 1.2.6 - New Wave Javascript
 *
 * Copyright (c) 2008 John Resig (jquery.com)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * Date: 2008-05-24 14:22:17 -0400 (Sat, 24 May 2008)
 * Rev: 5685
 */
eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(H(){J w=1b.4M,3m$=1b.$;J D=1b.4M=1b.$=H(a,b){I 2B D.17.5j(a,b)};J u=/^[^<]*(<(.|\\s)+>)[^>]*$|^#(\\w+)$/,62=/^.[^:#\\[\\.]*$/,12;D.17=D.44={5j:H(d,b){d=d||S;G(d.16){7[0]=d;7.K=1;I 7}G(1j d=="23"){J c=u.2D(d);G(c&&(c[1]||!b)){G(c[1])d=D.4h([c[1]],b);N{J a=S.61(c[3]);G(a){G(a.2v!=c[3])I D().2q(d);I D(a)}d=[]}}N I D(b).2q(d)}N G(D.1D(d))I D(S)[D.17.27?"27":"43"](d);I 7.6Y(D.2d(d))},5w:"1.2.6",8G:H(){I 7.K},K:0,3p:H(a){I a==12?D.2d(7):7[a]},2I:H(b){J a=D(b);a.5n=7;I a},6Y:H(a){7.K=0;2p.44.1p.1w(7,a);I 7},P:H(a,b){I D.P(7,a,b)},5i:H(b){J a=-1;I D.2L(b&&b.5w?b[0]:b,7)},1K:H(c,a,b){J d=c;G(c.1q==56)G(a===12)I 7[0]&&D[b||"1K"](7[0],c);N{d={};d[c]=a}I 7.P(H(i){R(c 1n d)D.1K(b?7.V:7,c,D.1i(7,d[c],b,i,c))})},1g:H(b,a){G((b==\'2h\'||b==\'1Z\')&&3d(a)<0)a=12;I 7.1K(b,a,"2a")},1r:H(b){G(1j b!="49"&&b!=U)I 7.4E().3v((7[0]&&7[0].2z||S).5F(b));J a="";D.P(b||7,H(){D.P(7.3t,H(){G(7.16!=8)a+=7.16!=1?7.76:D.17.1r([7])})});I a},5z:H(b){G(7[0])D(b,7[0].2z).5y().39(7[0]).2l(H(){J a=7;1B(a.1x)a=a.1x;I a}).3v(7);I 7},8Y:H(a){I 7.P(H(){D(7).6Q().5z(a)})},8R:H(a){I 7.P(H(){D(7).5z(a)})},3v:H(){I 7.3W(19,M,Q,H(a){G(7.16==1)7.3U(a)})},6F:H(){I 7.3W(19,M,M,H(a){G(7.16==1)7.39(a,7.1x)})},6E:H(){I 7.3W(19,Q,Q,H(a){7.1d.39(a,7)})},5q:H(){I 7.3W(19,Q,M,H(a){7.1d.39(a,7.2H)})},3l:H(){I 7.5n||D([])},2q:H(b){J c=D.2l(7,H(a){I D.2q(b,a)});I 7.2I(/[^+>] [^+>]/.11(b)||b.1h("..")>-1?D.4r(c):c)},5y:H(e){J f=7.2l(H(){G(D.14.1f&&!D.4n(7)){J a=7.6o(M),5h=S.3h("1v");5h.3U(a);I D.4h([5h.4H])[0]}N I 7.6o(M)});J d=f.2q("*").5c().P(H(){G(7[E]!=12)7[E]=U});G(e===M)7.2q("*").5c().P(H(i){G(7.16==3)I;J c=D.L(7,"3w");R(J a 1n c)R(J b 1n c[a])D.W.1e(d[i],a,c[a][b],c[a][b].L)});I f},1E:H(b){I 7.2I(D.1D(b)&&D.3C(7,H(a,i){I b.1k(a,i)})||D.3g(b,7))},4Y:H(b){G(b.1q==56)G(62.11(b))I 7.2I(D.3g(b,7,M));N b=D.3g(b,7);J a=b.K&&b[b.K-1]!==12&&!b.16;I 7.1E(H(){I a?D.2L(7,b)<0:7!=b})},1e:H(a){I 7.2I(D.4r(D.2R(7.3p(),1j a==\'23\'?D(a):D.2d(a))))},3F:H(a){I!!a&&D.3g(a,7).K>0},7T:H(a){I 7.3F("."+a)},6e:H(b){G(b==12){G(7.K){J c=7[0];G(D.Y(c,"2A")){J e=c.64,63=[],15=c.15,2V=c.O=="2A-2V";G(e<0)I U;R(J i=2V?e:0,2f=2V?e+1:15.K;i<2f;i++){J d=15[i];G(d.2W){b=D.14.1f&&!d.at.2x.an?d.1r:d.2x;G(2V)I b;63.1p(b)}}I 63}N I(7[0].2x||"").1o(/\\r/g,"")}I 12}G(b.1q==4L)b+=\'\';I 7.P(H(){G(7.16!=1)I;G(b.1q==2p&&/5O|5L/.11(7.O))7.4J=(D.2L(7.2x,b)>=0||D.2L(7.34,b)>=0);N G(D.Y(7,"2A")){J a=D.2d(b);D("9R",7).P(H(){7.2W=(D.2L(7.2x,a)>=0||D.2L(7.1r,a)>=0)});G(!a.K)7.64=-1}N 7.2x=b})},2K:H(a){I a==12?(7[0]?7[0].4H:U):7.4E().3v(a)},7b:H(a){I 7.5q(a).21()},79:H(i){I 7.3s(i,i+1)},3s:H(){I 7.2I(2p.44.3s.1w(7,19))},2l:H(b){I 7.2I(D.2l(7,H(a,i){I b.1k(a,i,a)}))},5c:H(){I 7.1e(7.5n)},L:H(d,b){J a=d.1R(".");a[1]=a[1]?"."+a[1]:"";G(b===12){J c=7.5C("9z"+a[1]+"!",[a[0]]);G(c===12&&7.K)c=D.L(7[0],d);I c===12&&a[1]?7.L(a[0]):c}N I 7.1P("9u"+a[1]+"!",[a[0],b]).P(H(){D.L(7,d,b)})},3b:H(a){I 7.P(H(){D.3b(7,a)})},3W:H(g,f,h,d){J e=7.K>1,3x;I 7.P(H(){G(!3x){3x=D.4h(g,7.2z);G(h)3x.9o()}J b=7;G(f&&D.Y(7,"1T")&&D.Y(3x[0],"4F"))b=7.3H("22")[0]||7.3U(7.2z.3h("22"));J c=D([]);D.P(3x,H(){J a=e?D(7).5y(M)[0]:7;G(D.Y(a,"1m"))c=c.1e(a);N{G(a.16==1)c=c.1e(D("1m",a).21());d.1k(b,a)}});c.P(6T)})}};D.17.5j.44=D.17;H 6T(i,a){G(a.4d)D.3Y({1a:a.4d,31:Q,1O:"1m"});N D.5u(a.1r||a.6O||a.4H||"");G(a.1d)a.1d.37(a)}H 1z(){I+2B 8J}D.1l=D.17.1l=H(){J b=19[0]||{},i=1,K=19.K,4x=Q,15;G(b.1q==8I){4x=b;b=19[1]||{};i=2}G(1j b!="49"&&1j b!="H")b={};G(K==i){b=7;--i}R(;i<K;i++)G((15=19[i])!=U)R(J c 1n 15){J a=b[c],2w=15[c];G(b===2w)6M;G(4x&&2w&&1j 2w=="49"&&!2w.16)b[c]=D.1l(4x,a||(2w.K!=U?[]:{}),2w);N G(2w!==12)b[c]=2w}I b};J E="4M"+1z(),6K=0,5r={},6G=/z-?5i|8B-?8A|1y|6B|8v-?1Z/i,3P=S.3P||{};D.1l({8u:H(a){1b.$=3m$;G(a)1b.4M=w;I D},1D:H(a){I!!a&&1j a!="23"&&!a.Y&&a.1q!=2p&&/^[\\s[]?H/.11(a+"")},4n:H(a){I a.1C&&!a.1c||a.2j&&a.2z&&!a.2z.1c},5u:H(a){a=D.3k(a);G(a){J b=S.3H("6w")[0]||S.1C,1m=S.3h("1m");1m.O="1r/4t";G(D.14.1f)1m.1r=a;N 1m.3U(S.5F(a));b.39(1m,b.1x);b.37(1m)}},Y:H(b,a){I b.Y&&b.Y.2r()==a.2r()},1Y:{},L:H(c,d,b){c=c==1b?5r:c;J a=c[E];G(!a)a=c[E]=++6K;G(d&&!D.1Y[a])D.1Y[a]={};G(b!==12)D.1Y[a][d]=b;I d?D.1Y[a][d]:a},3b:H(c,b){c=c==1b?5r:c;J a=c[E];G(b){G(D.1Y[a]){2U D.1Y[a][b];b="";R(b 1n D.1Y[a])1X;G(!b)D.3b(c)}}N{1U{2U c[E]}1V(e){G(c.5l)c.5l(E)}2U D.1Y[a]}},P:H(d,a,c){J e,i=0,K=d.K;G(c){G(K==12){R(e 1n d)G(a.1w(d[e],c)===Q)1X}N R(;i<K;)G(a.1w(d[i++],c)===Q)1X}N{G(K==12){R(e 1n d)G(a.1k(d[e],e,d[e])===Q)1X}N R(J b=d[0];i<K&&a.1k(b,i,b)!==Q;b=d[++i]){}}I d},1i:H(b,a,c,i,d){G(D.1D(a))a=a.1k(b,i);I a&&a.1q==4L&&c=="2a"&&!6G.11(d)?a+"2X":a},1F:{1e:H(c,b){D.P((b||"").1R(/\\s+/),H(i,a){G(c.16==1&&!D.1F.3T(c.1F,a))c.1F+=(c.1F?" ":"")+a})},21:H(c,b){G(c.16==1)c.1F=b!=12?D.3C(c.1F.1R(/\\s+/),H(a){I!D.1F.3T(b,a)}).6s(" "):""},3T:H(b,a){I D.2L(a,(b.1F||b).6r().1R(/\\s+/))>-1}},6q:H(b,c,a){J e={};R(J d 1n c){e[d]=b.V[d];b.V[d]=c[d]}a.1k(b);R(J d 1n c)b.V[d]=e[d]},1g:H(d,e,c){G(e=="2h"||e=="1Z"){J b,3X={30:"5x",5g:"1G",18:"3I"},35=e=="2h"?["5e","6k"]:["5G","6i"];H 5b(){b=e=="2h"?d.8f:d.8c;J a=0,2C=0;D.P(35,H(){a+=3d(D.2a(d,"57"+7,M))||0;2C+=3d(D.2a(d,"2C"+7+"4b",M))||0});b-=29.83(a+2C)}G(D(d).3F(":4j"))5b();N D.6q(d,3X,5b);I 29.2f(0,b)}I D.2a(d,e,c)},2a:H(f,l,k){J e,V=f.V;H 3E(b){G(!D.14.2k)I Q;J a=3P.54(b,U);I!a||a.52("3E")==""}G(l=="1y"&&D.14.1f){e=D.1K(V,"1y");I e==""?"1":e}G(D.14.2G&&l=="18"){J d=V.50;V.50="0 7Y 7W";V.50=d}G(l.1I(/4i/i))l=y;G(!k&&V&&V[l])e=V[l];N G(3P.54){G(l.1I(/4i/i))l="4i";l=l.1o(/([A-Z])/g,"-$1").3y();J c=3P.54(f,U);G(c&&!3E(f))e=c.52(l);N{J g=[],2E=[],a=f,i=0;R(;a&&3E(a);a=a.1d)2E.6h(a);R(;i<2E.K;i++)G(3E(2E[i])){g[i]=2E[i].V.18;2E[i].V.18="3I"}e=l=="18"&&g[2E.K-1]!=U?"2F":(c&&c.52(l))||"";R(i=0;i<g.K;i++)G(g[i]!=U)2E[i].V.18=g[i]}G(l=="1y"&&e=="")e="1"}N G(f.4g){J h=l.1o(/\\-(\\w)/g,H(a,b){I b.2r()});e=f.4g[l]||f.4g[h];G(!/^\\d+(2X)?$/i.11(e)&&/^\\d/.11(e)){J j=V.1A,66=f.65.1A;f.65.1A=f.4g.1A;V.1A=e||0;e=V.aM+"2X";V.1A=j;f.65.1A=66}}I e},4h:H(l,h){J k=[];h=h||S;G(1j h.3h==\'12\')h=h.2z||h[0]&&h[0].2z||S;D.P(l,H(i,d){G(!d)I;G(d.1q==4L)d+=\'\';G(1j d=="23"){d=d.1o(/(<(\\w+)[^>]*?)\\/>/g,H(b,a,c){I c.1I(/^(aK|4f|7E|aG|4T|7A|aB|3n|az|ay|av)$/i)?b:a+"></"+c+">"});J f=D.3k(d).3y(),1v=h.3h("1v");J e=!f.1h("<au")&&[1,"<2A 7w=\'7w\'>","</2A>"]||!f.1h("<ar")&&[1,"<7v>","</7v>"]||f.1I(/^<(aq|22|am|ak|ai)/)&&[1,"<1T>","</1T>"]||!f.1h("<4F")&&[2,"<1T><22>","</22></1T>"]||(!f.1h("<af")||!f.1h("<ad"))&&[3,"<1T><22><4F>","</4F></22></1T>"]||!f.1h("<7E")&&[2,"<1T><22></22><7q>","</7q></1T>"]||D.14.1f&&[1,"1v<1v>","</1v>"]||[0,"",""];1v.4H=e[1]+d+e[2];1B(e[0]--)1v=1v.5T;G(D.14.1f){J g=!f.1h("<1T")&&f.1h("<22")<0?1v.1x&&1v.1x.3t:e[1]=="<1T>"&&f.1h("<22")<0?1v.3t:[];R(J j=g.K-1;j>=0;--j)G(D.Y(g[j],"22")&&!g[j].3t.K)g[j].1d.37(g[j]);G(/^\\s/.11(d))1v.39(h.5F(d.1I(/^\\s*/)[0]),1v.1x)}d=D.2d(1v.3t)}G(d.K===0&&(!D.Y(d,"3V")&&!D.Y(d,"2A")))I;G(d[0]==12||D.Y(d,"3V")||d.15)k.1p(d);N k=D.2R(k,d)});I k},1K:H(d,f,c){G(!d||d.16==3||d.16==8)I 12;J e=!D.4n(d),40=c!==12,1f=D.14.1f;f=e&&D.3X[f]||f;G(d.2j){J g=/5Q|4d|V/.11(f);G(f=="2W"&&D.14.2k)d.1d.64;G(f 1n d&&e&&!g){G(40){G(f=="O"&&D.Y(d,"4T")&&d.1d)7p"O a3 a1\'t 9V 9U";d[f]=c}G(D.Y(d,"3V")&&d.7i(f))I d.7i(f).76;I d[f]}G(1f&&e&&f=="V")I D.1K(d.V,"9T",c);G(40)d.9Q(f,""+c);J h=1f&&e&&g?d.4G(f,2):d.4G(f);I h===U?12:h}G(1f&&f=="1y"){G(40){d.6B=1;d.1E=(d.1E||"").1o(/7f\\([^)]*\\)/,"")+(3r(c)+\'\'=="9L"?"":"7f(1y="+c*7a+")")}I d.1E&&d.1E.1h("1y=")>=0?(3d(d.1E.1I(/1y=([^)]*)/)[1])/7a)+\'\':""}f=f.1o(/-([a-z])/9H,H(a,b){I b.2r()});G(40)d[f]=c;I d[f]},3k:H(a){I(a||"").1o(/^\\s+|\\s+$/g,"")},2d:H(b){J a=[];G(b!=U){J i=b.K;G(i==U||b.1R||b.4I||b.1k)a[0]=b;N 1B(i)a[--i]=b[i]}I a},2L:H(b,a){R(J i=0,K=a.K;i<K;i++)G(a[i]===b)I i;I-1},2R:H(a,b){J i=0,T,2S=a.K;G(D.14.1f){1B(T=b[i++])G(T.16!=8)a[2S++]=T}N 1B(T=b[i++])a[2S++]=T;I a},4r:H(a){J c=[],2o={};1U{R(J i=0,K=a.K;i<K;i++){J b=D.L(a[i]);G(!2o[b]){2o[b]=M;c.1p(a[i])}}}1V(e){c=a}I c},3C:H(c,a,d){J b=[];R(J i=0,K=c.K;i<K;i++)G(!d!=!a(c[i],i))b.1p(c[i]);I b},2l:H(d,a){J c=[];R(J i=0,K=d.K;i<K;i++){J b=a(d[i],i);G(b!=U)c[c.K]=b}I c.7d.1w([],c)}});J v=9B.9A.3y();D.14={5B:(v.1I(/.+(?:9y|9x|9w|9v)[\\/: ]([\\d.]+)/)||[])[1],2k:/75/.11(v),2G:/2G/.11(v),1f:/1f/.11(v)&&!/2G/.11(v),42:/42/.11(v)&&!/(9s|75)/.11(v)};J y=D.14.1f?"7o":"72";D.1l({71:!D.14.1f||S.70=="6Z",3X:{"R":"9n","9k":"1F","4i":y,72:y,7o:y,9h:"9f",9e:"9d",9b:"99"}});D.P({6W:H(a){I a.1d},97:H(a){I D.4S(a,"1d")},95:H(a){I D.3a(a,2,"2H")},91:H(a){I D.3a(a,2,"4l")},8Z:H(a){I D.4S(a,"2H")},8X:H(a){I D.4S(a,"4l")},8W:H(a){I D.5v(a.1d.1x,a)},8V:H(a){I D.5v(a.1x)},6Q:H(a){I D.Y(a,"8U")?a.8T||a.8S.S:D.2d(a.3t)}},H(c,d){D.17[c]=H(b){J a=D.2l(7,d);G(b&&1j b=="23")a=D.3g(b,a);I 7.2I(D.4r(a))}});D.P({6P:"3v",8Q:"6F",39:"6E",8P:"5q",8O:"7b"},H(c,b){D.17[c]=H(){J a=19;I 7.P(H(){R(J i=0,K=a.K;i<K;i++)D(a[i])[b](7)})}});D.P({8N:H(a){D.1K(7,a,"");G(7.16==1)7.5l(a)},8M:H(a){D.1F.1e(7,a)},8L:H(a){D.1F.21(7,a)},8K:H(a){D.1F[D.1F.3T(7,a)?"21":"1e"](7,a)},21:H(a){G(!a||D.1E(a,[7]).r.K){D("*",7).1e(7).P(H(){D.W.21(7);D.3b(7)});G(7.1d)7.1d.37(7)}},4E:H(){D(">*",7).21();1B(7.1x)7.37(7.1x)}},H(a,b){D.17[a]=H(){I 7.P(b,19)}});D.P(["6N","4b"],H(i,c){J b=c.3y();D.17[b]=H(a){I 7[0]==1b?D.14.2G&&S.1c["5t"+c]||D.14.2k&&1b["5s"+c]||S.70=="6Z"&&S.1C["5t"+c]||S.1c["5t"+c]:7[0]==S?29.2f(29.2f(S.1c["4y"+c],S.1C["4y"+c]),29.2f(S.1c["2i"+c],S.1C["2i"+c])):a==12?(7.K?D.1g(7[0],b):U):7.1g(b,a.1q==56?a:a+"2X")}});H 25(a,b){I a[0]&&3r(D.2a(a[0],b,M),10)||0}J C=D.14.2k&&3r(D.14.5B)<8H?"(?:[\\\\w*3m-]|\\\\\\\\.)":"(?:[\\\\w\\8F-\\8E*3m-]|\\\\\\\\.)",6L=2B 4v("^>\\\\s*("+C+"+)"),6J=2B 4v("^("+C+"+)(#)("+C+"+)"),6I=2B 4v("^([#.]?)("+C+"*)");D.1l({6H:{"":H(a,i,m){I m[2]=="*"||D.Y(a,m[2])},"#":H(a,i,m){I a.4G("2v")==m[2]},":":{8D:H(a,i,m){I i<m[3]-0},8C:H(a,i,m){I i>m[3]-0},3a:H(a,i,m){I m[3]-0==i},79:H(a,i,m){I m[3]-0==i},3o:H(a,i){I i==0},3S:H(a,i,m,r){I i==r.K-1},6D:H(a,i){I i%2==0},6C:H(a,i){I i%2},"3o-4u":H(a){I a.1d.3H("*")[0]==a},"3S-4u":H(a){I D.3a(a.1d.5T,1,"4l")==a},"8z-4u":H(a){I!D.3a(a.1d.5T,2,"4l")},6W:H(a){I a.1x},4E:H(a){I!a.1x},8y:H(a,i,m){I(a.6O||a.8x||D(a).1r()||"").1h(m[3])>=0},4j:H(a){I"1G"!=a.O&&D.1g(a,"18")!="2F"&&D.1g(a,"5g")!="1G"},1G:H(a){I"1G"==a.O||D.1g(a,"18")=="2F"||D.1g(a,"5g")=="1G"},8w:H(a){I!a.3R},3R:H(a){I a.3R},4J:H(a){I a.4J},2W:H(a){I a.2W||D.1K(a,"2W")},1r:H(a){I"1r"==a.O},5O:H(a){I"5O"==a.O},5L:H(a){I"5L"==a.O},5p:H(a){I"5p"==a.O},3Q:H(a){I"3Q"==a.O},5o:H(a){I"5o"==a.O},6A:H(a){I"6A"==a.O},6z:H(a){I"6z"==a.O},2s:H(a){I"2s"==a.O||D.Y(a,"2s")},4T:H(a){I/4T|2A|6y|2s/i.11(a.Y)},3T:H(a,i,m){I D.2q(m[3],a).K},8t:H(a){I/h\\d/i.11(a.Y)},8s:H(a){I D.3C(D.3O,H(b){I a==b.T}).K}}},6x:[/^(\\[) *@?([\\w-]+) *([!*$^~=]*) *(\'?"?)(.*?)\\4 *\\]/,/^(:)([\\w-]+)\\("?\'?(.*?(\\(.*?\\))?[^(]*?)"?\'?\\)/,2B 4v("^([:.#]*)("+C+"+)")],3g:H(a,c,b){J d,1t=[];1B(a&&a!=d){d=a;J f=D.1E(a,c,b);a=f.t.1o(/^\\s*,\\s*/,"");1t=b?c=f.r:D.2R(1t,f.r)}I 1t},2q:H(t,o){G(1j t!="23")I[t];G(o&&o.16!=1&&o.16!=9)I[];o=o||S;J d=[o],2o=[],3S,Y;1B(t&&3S!=t){J r=[];3S=t;t=D.3k(t);J l=Q,3j=6L,m=3j.2D(t);G(m){Y=m[1].2r();R(J i=0;d[i];i++)R(J c=d[i].1x;c;c=c.2H)G(c.16==1&&(Y=="*"||c.Y.2r()==Y))r.1p(c);d=r;t=t.1o(3j,"");G(t.1h(" ")==0)6M;l=M}N{3j=/^([>+~])\\s*(\\w*)/i;G((m=3j.2D(t))!=U){r=[];J k={};Y=m[2].2r();m=m[1];R(J j=0,3i=d.K;j<3i;j++){J n=m=="~"||m=="+"?d[j].2H:d[j].1x;R(;n;n=n.2H)G(n.16==1){J g=D.L(n);G(m=="~"&&k[g])1X;G(!Y||n.Y.2r()==Y){G(m=="~")k[g]=M;r.1p(n)}G(m=="+")1X}}d=r;t=D.3k(t.1o(3j,""));l=M}}G(t&&!l){G(!t.1h(",")){G(o==d[0])d.4s();2o=D.2R(2o,d);r=d=[o];t=" "+t.6v(1,t.K)}N{J h=6J;J m=h.2D(t);G(m){m=[0,m[2],m[3],m[1]]}N{h=6I;m=h.2D(t)}m[2]=m[2].1o(/\\\\/g,"");J f=d[d.K-1];G(m[1]=="#"&&f&&f.61&&!D.4n(f)){J p=f.61(m[2]);G((D.14.1f||D.14.2G)&&p&&1j p.2v=="23"&&p.2v!=m[2])p=D(\'[@2v="\'+m[2]+\'"]\',f)[0];d=r=p&&(!m[3]||D.Y(p,m[3]))?[p]:[]}N{R(J i=0;d[i];i++){J a=m[1]=="#"&&m[3]?m[3]:m[1]!=""||m[0]==""?"*":m[2];G(a=="*"&&d[i].Y.3y()=="49")a="3n";r=D.2R(r,d[i].3H(a))}G(m[1]==".")r=D.5m(r,m[2]);G(m[1]=="#"){J e=[];R(J i=0;r[i];i++)G(r[i].4G("2v")==m[2]){e=[r[i]];1X}r=e}d=r}t=t.1o(h,"")}}G(t){J b=D.1E(t,r);d=r=b.r;t=D.3k(b.t)}}G(t)d=[];G(d&&o==d[0])d.4s();2o=D.2R(2o,d);I 2o},5m:H(r,m,a){m=" "+m+" ";J c=[];R(J i=0;r[i];i++){J b=(" "+r[i].1F+" ").1h(m)>=0;G(!a&&b||a&&!b)c.1p(r[i])}I c},1E:H(t,r,h){J d;1B(t&&t!=d){d=t;J p=D.6x,m;R(J i=0;p[i];i++){m=p[i].2D(t);G(m){t=t.8r(m[0].K);m[2]=m[2].1o(/\\\\/g,"");1X}}G(!m)1X;G(m[1]==":"&&m[2]=="4Y")r=62.11(m[3])?D.1E(m[3],r,M).r:D(r).4Y(m[3]);N G(m[1]==".")r=D.5m(r,m[2],h);N G(m[1]=="["){J g=[],O=m[3];R(J i=0,3i=r.K;i<3i;i++){J a=r[i],z=a[D.3X[m[2]]||m[2]];G(z==U||/5Q|4d|2W/.11(m[2]))z=D.1K(a,m[2])||\'\';G((O==""&&!!z||O=="="&&z==m[5]||O=="!="&&z!=m[5]||O=="^="&&z&&!z.1h(m[5])||O=="$="&&z.6v(z.K-m[5].K)==m[5]||(O=="*="||O=="~=")&&z.1h(m[5])>=0)^h)g.1p(a)}r=g}N G(m[1]==":"&&m[2]=="3a-4u"){J e={},g=[],11=/(-?)(\\d*)n((?:\\+|-)?\\d*)/.2D(m[3]=="6D"&&"2n"||m[3]=="6C"&&"2n+1"||!/\\D/.11(m[3])&&"8q+"+m[3]||m[3]),3o=(11[1]+(11[2]||1))-0,d=11[3]-0;R(J i=0,3i=r.K;i<3i;i++){J j=r[i],1d=j.1d,2v=D.L(1d);G(!e[2v]){J c=1;R(J n=1d.1x;n;n=n.2H)G(n.16==1)n.4q=c++;e[2v]=M}J b=Q;G(3o==0){G(j.4q==d)b=M}N G((j.4q-d)%3o==0&&(j.4q-d)/3o>=0)b=M;G(b^h)g.1p(j)}r=g}N{J f=D.6H[m[1]];G(1j f=="49")f=f[m[2]];G(1j f=="23")f=6u("Q||H(a,i){I "+f+";}");r=D.3C(r,H(a,i){I f(a,i,m,r)},h)}}I{r:r,t:t}},4S:H(b,c){J a=[],1t=b[c];1B(1t&&1t!=S){G(1t.16==1)a.1p(1t);1t=1t[c]}I a},3a:H(a,e,c,b){e=e||1;J d=0;R(;a;a=a[c])G(a.16==1&&++d==e)1X;I a},5v:H(n,a){J r=[];R(;n;n=n.2H){G(n.16==1&&n!=a)r.1p(n)}I r}});D.W={1e:H(f,i,g,e){G(f.16==3||f.16==8)I;G(D.14.1f&&f.4I)f=1b;G(!g.24)g.24=7.24++;G(e!=12){J h=g;g=7.3M(h,H(){I h.1w(7,19)});g.L=e}J j=D.L(f,"3w")||D.L(f,"3w",{}),1H=D.L(f,"1H")||D.L(f,"1H",H(){G(1j D!="12"&&!D.W.5k)I D.W.1H.1w(19.3L.T,19)});1H.T=f;D.P(i.1R(/\\s+/),H(c,b){J a=b.1R(".");b=a[0];g.O=a[1];J d=j[b];G(!d){d=j[b]={};G(!D.W.2t[b]||D.W.2t[b].4p.1k(f)===Q){G(f.3K)f.3K(b,1H,Q);N G(f.6t)f.6t("4o"+b,1H)}}d[g.24]=g;D.W.26[b]=M});f=U},24:1,26:{},21:H(e,h,f){G(e.16==3||e.16==8)I;J i=D.L(e,"3w"),1L,5i;G(i){G(h==12||(1j h=="23"&&h.8p(0)=="."))R(J g 1n i)7.21(e,g+(h||""));N{G(h.O){f=h.2y;h=h.O}D.P(h.1R(/\\s+/),H(b,a){J c=a.1R(".");a=c[0];G(i[a]){G(f)2U i[a][f.24];N R(f 1n i[a])G(!c[1]||i[a][f].O==c[1])2U i[a][f];R(1L 1n i[a])1X;G(!1L){G(!D.W.2t[a]||D.W.2t[a].4A.1k(e)===Q){G(e.6p)e.6p(a,D.L(e,"1H"),Q);N G(e.6n)e.6n("4o"+a,D.L(e,"1H"))}1L=U;2U i[a]}}})}R(1L 1n i)1X;G(!1L){J d=D.L(e,"1H");G(d)d.T=U;D.3b(e,"3w");D.3b(e,"1H")}}},1P:H(h,c,f,g,i){c=D.2d(c);G(h.1h("!")>=0){h=h.3s(0,-1);J a=M}G(!f){G(7.26[h])D("*").1e([1b,S]).1P(h,c)}N{G(f.16==3||f.16==8)I 12;J b,1L,17=D.1D(f[h]||U),W=!c[0]||!c[0].32;G(W){c.6h({O:h,2J:f,32:H(){},3J:H(){},4C:1z()});c[0][E]=M}c[0].O=h;G(a)c[0].6m=M;J d=D.L(f,"1H");G(d)b=d.1w(f,c);G((!17||(D.Y(f,\'a\')&&h=="4V"))&&f["4o"+h]&&f["4o"+h].1w(f,c)===Q)b=Q;G(W)c.4s();G(i&&D.1D(i)){1L=i.1w(f,b==U?c:c.7d(b));G(1L!==12)b=1L}G(17&&g!==Q&&b!==Q&&!(D.Y(f,\'a\')&&h=="4V")){7.5k=M;1U{f[h]()}1V(e){}}7.5k=Q}I b},1H:H(b){J a,1L,38,5f,4m;b=19[0]=D.W.6l(b||1b.W);38=b.O.1R(".");b.O=38[0];38=38[1];5f=!38&&!b.6m;4m=(D.L(7,"3w")||{})[b.O];R(J j 1n 4m){J c=4m[j];G(5f||c.O==38){b.2y=c;b.L=c.L;1L=c.1w(7,19);G(a!==Q)a=1L;G(1L===Q){b.32();b.3J()}}}I a},6l:H(b){G(b[E]==M)I b;J d=b;b={8o:d};J c="8n 8m 8l 8k 2s 8j 47 5d 6j 5E 8i L 8h 8g 4K 2y 5a 59 8e 8b 58 6f 8a 88 4k 87 86 84 6d 2J 4C 6c O 82 81 35".1R(" ");R(J i=c.K;i;i--)b[c[i]]=d[c[i]];b[E]=M;b.32=H(){G(d.32)d.32();d.80=Q};b.3J=H(){G(d.3J)d.3J();d.7Z=M};b.4C=b.4C||1z();G(!b.2J)b.2J=b.6d||S;G(b.2J.16==3)b.2J=b.2J.1d;G(!b.4k&&b.4K)b.4k=b.4K==b.2J?b.6c:b.4K;G(b.58==U&&b.5d!=U){J a=S.1C,1c=S.1c;b.58=b.5d+(a&&a.2e||1c&&1c.2e||0)-(a.6b||0);b.6f=b.6j+(a&&a.2c||1c&&1c.2c||0)-(a.6a||0)}G(!b.35&&((b.47||b.47===0)?b.47:b.5a))b.35=b.47||b.5a;G(!b.59&&b.5E)b.59=b.5E;G(!b.35&&b.2s)b.35=(b.2s&1?1:(b.2s&2?3:(b.2s&4?2:0)));I b},3M:H(a,b){b.24=a.24=a.24||b.24||7.24++;I b},2t:{27:{4p:H(){55();I},4A:H(){I}},3D:{4p:H(){G(D.14.1f)I Q;D(7).2O("53",D.W.2t.3D.2y);I M},4A:H(){G(D.14.1f)I Q;D(7).4e("53",D.W.2t.3D.2y);I M},2y:H(a){G(F(a,7))I M;a.O="3D";I D.W.1H.1w(7,19)}},3N:{4p:H(){G(D.14.1f)I Q;D(7).2O("51",D.W.2t.3N.2y);I M},4A:H(){G(D.14.1f)I Q;D(7).4e("51",D.W.2t.3N.2y);I M},2y:H(a){G(F(a,7))I M;a.O="3N";I D.W.1H.1w(7,19)}}}};D.17.1l({2O:H(c,a,b){I c=="4X"?7.2V(c,a,b):7.P(H(){D.W.1e(7,c,b||a,b&&a)})},2V:H(d,b,c){J e=D.W.3M(c||b,H(a){D(7).4e(a,e);I(c||b).1w(7,19)});I 7.P(H(){D.W.1e(7,d,e,c&&b)})},4e:H(a,b){I 7.P(H(){D.W.21(7,a,b)})},1P:H(c,a,b){I 7.P(H(){D.W.1P(c,a,7,M,b)})},5C:H(c,a,b){I 7[0]&&D.W.1P(c,a,7[0],Q,b)},2m:H(b){J c=19,i=1;1B(i<c.K)D.W.3M(b,c[i++]);I 7.4V(D.W.3M(b,H(a){7.4Z=(7.4Z||0)%i;a.32();I c[7.4Z++].1w(7,19)||Q}))},7X:H(a,b){I 7.2O(\'3D\',a).2O(\'3N\',b)},27:H(a){55();G(D.2Q)a.1k(S,D);N D.3A.1p(H(){I a.1k(7,D)});I 7}});D.1l({2Q:Q,3A:[],27:H(){G(!D.2Q){D.2Q=M;G(D.3A){D.P(D.3A,H(){7.1k(S)});D.3A=U}D(S).5C("27")}}});J x=Q;H 55(){G(x)I;x=M;G(S.3K&&!D.14.2G)S.3K("69",D.27,Q);G(D.14.1f&&1b==1S)(H(){G(D.2Q)I;1U{S.1C.7V("1A")}1V(3e){3B(19.3L,0);I}D.27()})();G(D.14.2G)S.3K("69",H(){G(D.2Q)I;R(J i=0;i<S.4W.K;i++)G(S.4W[i].3R){3B(19.3L,0);I}D.27()},Q);G(D.14.2k){J a;(H(){G(D.2Q)I;G(S.3f!="68"&&S.3f!="1J"){3B(19.3L,0);I}G(a===12)a=D("V, 7A[7U=7S]").K;G(S.4W.K!=a){3B(19.3L,0);I}D.27()})()}D.W.1e(1b,"43",D.27)}D.P(("7R,7Q,43,85,4y,4X,4V,7P,"+"7O,7N,89,53,51,7M,2A,"+"5o,7L,7K,8d,3e").1R(","),H(i,b){D.17[b]=H(a){I a?7.2O(b,a):7.1P(b)}});J F=H(a,c){J b=a.4k;1B(b&&b!=c)1U{b=b.1d}1V(3e){b=c}I b==c};D(1b).2O("4X",H(){D("*").1e(S).4e()});D.17.1l({67:D.17.43,43:H(g,d,c){G(1j g!=\'23\')I 7.67(g);J e=g.1h(" ");G(e>=0){J i=g.3s(e,g.K);g=g.3s(0,e)}c=c||H(){};J f="2P";G(d)G(D.1D(d)){c=d;d=U}N{d=D.3n(d);f="6g"}J h=7;D.3Y({1a:g,O:f,1O:"2K",L:d,1J:H(a,b){G(b=="1W"||b=="7J")h.2K(i?D("<1v/>").3v(a.4U.1o(/<1m(.|\\s)*?\\/1m>/g,"")).2q(i):a.4U);h.P(c,[a.4U,b,a])}});I 7},aL:H(){I D.3n(7.7I())},7I:H(){I 7.2l(H(){I D.Y(7,"3V")?D.2d(7.aH):7}).1E(H(){I 7.34&&!7.3R&&(7.4J||/2A|6y/i.11(7.Y)||/1r|1G|3Q/i.11(7.O))}).2l(H(i,c){J b=D(7).6e();I b==U?U:b.1q==2p?D.2l(b,H(a,i){I{34:c.34,2x:a}}):{34:c.34,2x:b}}).3p()}});D.P("7H,7G,7F,7D,7C,7B".1R(","),H(i,o){D.17[o]=H(f){I 7.2O(o,f)}});J B=1z();D.1l({3p:H(d,b,a,c){G(D.1D(b)){a=b;b=U}I D.3Y({O:"2P",1a:d,L:b,1W:a,1O:c})},aE:H(b,a){I D.3p(b,U,a,"1m")},aD:H(c,b,a){I D.3p(c,b,a,"3z")},aC:H(d,b,a,c){G(D.1D(b)){a=b;b={}}I D.3Y({O:"6g",1a:d,L:b,1W:a,1O:c})},aA:H(a){D.1l(D.60,a)},60:{1a:5Z.5Q,26:M,O:"2P",2T:0,7z:"4R/x-ax-3V-aw",7x:M,31:M,L:U,5Y:U,3Q:U,4Q:{2N:"4R/2N, 1r/2N",2K:"1r/2K",1m:"1r/4t, 4R/4t",3z:"4R/3z, 1r/4t",1r:"1r/as",4w:"*/*"}},4z:{},3Y:H(s){s=D.1l(M,s,D.1l(M,{},D.60,s));J g,2Z=/=\\?(&|$)/g,1u,L,O=s.O.2r();G(s.L&&s.7x&&1j s.L!="23")s.L=D.3n(s.L);G(s.1O=="4P"){G(O=="2P"){G(!s.1a.1I(2Z))s.1a+=(s.1a.1I(/\\?/)?"&":"?")+(s.4P||"7u")+"=?"}N G(!s.L||!s.L.1I(2Z))s.L=(s.L?s.L+"&":"")+(s.4P||"7u")+"=?";s.1O="3z"}G(s.1O=="3z"&&(s.L&&s.L.1I(2Z)||s.1a.1I(2Z))){g="4P"+B++;G(s.L)s.L=(s.L+"").1o(2Z,"="+g+"$1");s.1a=s.1a.1o(2Z,"="+g+"$1");s.1O="1m";1b[g]=H(a){L=a;1W();1J();1b[g]=12;1U{2U 1b[g]}1V(e){}G(i)i.37(h)}}G(s.1O=="1m"&&s.1Y==U)s.1Y=Q;G(s.1Y===Q&&O=="2P"){J j=1z();J k=s.1a.1o(/(\\?|&)3m=.*?(&|$)/,"$ap="+j+"$2");s.1a=k+((k==s.1a)?(s.1a.1I(/\\?/)?"&":"?")+"3m="+j:"")}G(s.L&&O=="2P"){s.1a+=(s.1a.1I(/\\?/)?"&":"?")+s.L;s.L=U}G(s.26&&!D.4O++)D.W.1P("7H");J n=/^(?:\\w+:)?\\/\\/([^\\/?#]+)/;G(s.1O=="1m"&&O=="2P"&&n.11(s.1a)&&n.2D(s.1a)[1]!=5Z.al){J i=S.3H("6w")[0];J h=S.3h("1m");h.4d=s.1a;G(s.7t)h.aj=s.7t;G(!g){J l=Q;h.ah=h.ag=H(){G(!l&&(!7.3f||7.3f=="68"||7.3f=="1J")){l=M;1W();1J();i.37(h)}}}i.3U(h);I 12}J m=Q;J c=1b.7s?2B 7s("ae.ac"):2B 7r();G(s.5Y)c.6R(O,s.1a,s.31,s.5Y,s.3Q);N c.6R(O,s.1a,s.31);1U{G(s.L)c.4B("ab-aa",s.7z);G(s.5S)c.4B("a9-5R-a8",D.4z[s.1a]||"a7, a6 a5 a4 5N:5N:5N a2");c.4B("X-9Z-9Y","7r");c.4B("9W",s.1O&&s.4Q[s.1O]?s.4Q[s.1O]+", */*":s.4Q.4w)}1V(e){}G(s.7m&&s.7m(c,s)===Q){s.26&&D.4O--;c.7l();I Q}G(s.26)D.W.1P("7B",[c,s]);J d=H(a){G(!m&&c&&(c.3f==4||a=="2T")){m=M;G(f){7k(f);f=U}1u=a=="2T"&&"2T"||!D.7j(c)&&"3e"||s.5S&&D.7h(c,s.1a)&&"7J"||"1W";G(1u=="1W"){1U{L=D.6X(c,s.1O,s.9S)}1V(e){1u="5J"}}G(1u=="1W"){J b;1U{b=c.5I("7g-5R")}1V(e){}G(s.5S&&b)D.4z[s.1a]=b;G(!g)1W()}N D.5H(s,c,1u);1J();G(s.31)c=U}};G(s.31){J f=4I(d,13);G(s.2T>0)3B(H(){G(c){c.7l();G(!m)d("2T")}},s.2T)}1U{c.9P(s.L)}1V(e){D.5H(s,c,U,e)}G(!s.31)d();H 1W(){G(s.1W)s.1W(L,1u);G(s.26)D.W.1P("7C",[c,s])}H 1J(){G(s.1J)s.1J(c,1u);G(s.26)D.W.1P("7F",[c,s]);G(s.26&&!--D.4O)D.W.1P("7G")}I c},5H:H(s,a,b,e){G(s.3e)s.3e(a,b,e);G(s.26)D.W.1P("7D",[a,s,e])},4O:0,7j:H(a){1U{I!a.1u&&5Z.9O=="5p:"||(a.1u>=7e&&a.1u<9N)||a.1u==7c||a.1u==9K||D.14.2k&&a.1u==12}1V(e){}I Q},7h:H(a,c){1U{J b=a.5I("7g-5R");I a.1u==7c||b==D.4z[c]||D.14.2k&&a.1u==12}1V(e){}I Q},6X:H(a,c,b){J d=a.5I("9J-O"),2N=c=="2N"||!c&&d&&d.1h("2N")>=0,L=2N?a.9I:a.4U;G(2N&&L.1C.2j=="5J")7p"5J";G(b)L=b(L,c);G(c=="1m")D.5u(L);G(c=="3z")L=6u("("+L+")");I L},3n:H(a){J s=[];G(a.1q==2p||a.5w)D.P(a,H(){s.1p(3u(7.34)+"="+3u(7.2x))});N R(J j 1n a)G(a[j]&&a[j].1q==2p)D.P(a[j],H(){s.1p(3u(j)+"="+3u(7))});N s.1p(3u(j)+"="+3u(D.1D(a[j])?a[j]():a[j]));I s.6s("&").1o(/%20/g,"+")}});D.17.1l({1N:H(c,b){I c?7.2g({1Z:"1N",2h:"1N",1y:"1N"},c,b):7.1E(":1G").P(H(){7.V.18=7.5D||"";G(D.1g(7,"18")=="2F"){J a=D("<"+7.2j+" />").6P("1c");7.V.18=a.1g("18");G(7.V.18=="2F")7.V.18="3I";a.21()}}).3l()},1M:H(b,a){I b?7.2g({1Z:"1M",2h:"1M",1y:"1M"},b,a):7.1E(":4j").P(H(){7.5D=7.5D||D.1g(7,"18");7.V.18="2F"}).3l()},78:D.17.2m,2m:H(a,b){I D.1D(a)&&D.1D(b)?7.78.1w(7,19):a?7.2g({1Z:"2m",2h:"2m",1y:"2m"},a,b):7.P(H(){D(7)[D(7).3F(":1G")?"1N":"1M"]()})},9G:H(b,a){I 7.2g({1Z:"1N"},b,a)},9F:H(b,a){I 7.2g({1Z:"1M"},b,a)},9E:H(b,a){I 7.2g({1Z:"2m"},b,a)},9D:H(b,a){I 7.2g({1y:"1N"},b,a)},9M:H(b,a){I 7.2g({1y:"1M"},b,a)},9C:H(c,a,b){I 7.2g({1y:a},c,b)},2g:H(k,j,i,g){J h=D.77(j,i,g);I 7[h.36===Q?"P":"36"](H(){G(7.16!=1)I Q;J f=D.1l({},h),p,1G=D(7).3F(":1G"),46=7;R(p 1n k){G(k[p]=="1M"&&1G||k[p]=="1N"&&!1G)I f.1J.1k(7);G(p=="1Z"||p=="2h"){f.18=D.1g(7,"18");f.33=7.V.33}}G(f.33!=U)7.V.33="1G";f.45=D.1l({},k);D.P(k,H(c,a){J e=2B D.28(46,f,c);G(/2m|1N|1M/.11(a))e[a=="2m"?1G?"1N":"1M":a](k);N{J b=a.6r().1I(/^([+-]=)?([\\d+-.]+)(.*)$/),2b=e.1t(M)||0;G(b){J d=3d(b[2]),2M=b[3]||"2X";G(2M!="2X"){46.V[c]=(d||1)+2M;2b=((d||1)/e.1t(M))*2b;46.V[c]=2b+2M}G(b[1])d=((b[1]=="-="?-1:1)*d)+2b;e.3G(2b,d,2M)}N e.3G(2b,a,"")}});I M})},36:H(a,b){G(D.1D(a)||(a&&a.1q==2p)){b=a;a="28"}G(!a||(1j a=="23"&&!b))I A(7[0],a);I 7.P(H(){G(b.1q==2p)A(7,a,b);N{A(7,a).1p(b);G(A(7,a).K==1)b.1k(7)}})},9X:H(b,c){J a=D.3O;G(b)7.36([]);7.P(H(){R(J i=a.K-1;i>=0;i--)G(a[i].T==7){G(c)a[i](M);a.7n(i,1)}});G(!c)7.5A();I 7}});J A=H(b,c,a){G(b){c=c||"28";J q=D.L(b,c+"36");G(!q||a)q=D.L(b,c+"36",D.2d(a))}I q};D.17.5A=H(a){a=a||"28";I 7.P(H(){J q=A(7,a);q.4s();G(q.K)q[0].1k(7)})};D.1l({77:H(b,a,c){J d=b&&b.1q==a0?b:{1J:c||!c&&a||D.1D(b)&&b,2u:b,41:c&&a||a&&a.1q!=9t&&a};d.2u=(d.2u&&d.2u.1q==4L?d.2u:D.28.5K[d.2u])||D.28.5K.74;d.5M=d.1J;d.1J=H(){G(d.36!==Q)D(7).5A();G(D.1D(d.5M))d.5M.1k(7)};I d},41:{73:H(p,n,b,a){I b+a*p},5P:H(p,n,b,a){I((-29.9r(p*29.9q)/2)+0.5)*a+b}},3O:[],48:U,28:H(b,c,a){7.15=c;7.T=b;7.1i=a;G(!c.3Z)c.3Z={}}});D.28.44={4D:H(){G(7.15.2Y)7.15.2Y.1k(7.T,7.1z,7);(D.28.2Y[7.1i]||D.28.2Y.4w)(7);G(7.1i=="1Z"||7.1i=="2h")7.T.V.18="3I"},1t:H(a){G(7.T[7.1i]!=U&&7.T.V[7.1i]==U)I 7.T[7.1i];J r=3d(D.1g(7.T,7.1i,a));I r&&r>-9p?r:3d(D.2a(7.T,7.1i))||0},3G:H(c,b,d){7.5V=1z();7.2b=c;7.3l=b;7.2M=d||7.2M||"2X";7.1z=7.2b;7.2S=7.4N=0;7.4D();J e=7;H t(a){I e.2Y(a)}t.T=7.T;D.3O.1p(t);G(D.48==U){D.48=4I(H(){J a=D.3O;R(J i=0;i<a.K;i++)G(!a[i]())a.7n(i--,1);G(!a.K){7k(D.48);D.48=U}},13)}},1N:H(){7.15.3Z[7.1i]=D.1K(7.T.V,7.1i);7.15.1N=M;7.3G(0,7.1t());G(7.1i=="2h"||7.1i=="1Z")7.T.V[7.1i]="9m";D(7.T).1N()},1M:H(){7.15.3Z[7.1i]=D.1K(7.T.V,7.1i);7.15.1M=M;7.3G(7.1t(),0)},2Y:H(a){J t=1z();G(a||t>7.15.2u+7.5V){7.1z=7.3l;7.2S=7.4N=1;7.4D();7.15.45[7.1i]=M;J b=M;R(J i 1n 7.15.45)G(7.15.45[i]!==M)b=Q;G(b){G(7.15.18!=U){7.T.V.33=7.15.33;7.T.V.18=7.15.18;G(D.1g(7.T,"18")=="2F")7.T.V.18="3I"}G(7.15.1M)7.T.V.18="2F";G(7.15.1M||7.15.1N)R(J p 1n 7.15.45)D.1K(7.T.V,p,7.15.3Z[p])}G(b)7.15.1J.1k(7.T);I Q}N{J n=t-7.5V;7.4N=n/7.15.2u;7.2S=D.41[7.15.41||(D.41.5P?"5P":"73")](7.4N,n,0,1,7.15.2u);7.1z=7.2b+((7.3l-7.2b)*7.2S);7.4D()}I M}};D.1l(D.28,{5K:{9l:9j,9i:7e,74:9g},2Y:{2e:H(a){a.T.2e=a.1z},2c:H(a){a.T.2c=a.1z},1y:H(a){D.1K(a.T.V,"1y",a.1z)},4w:H(a){a.T.V[a.1i]=a.1z+a.2M}}});D.17.2i=H(){J b=0,1S=0,T=7[0],3q;G(T)ao(D.14){J d=T.1d,4a=T,1s=T.1s,1Q=T.2z,5U=2k&&3r(5B)<9c&&!/9a/i.11(v),1g=D.2a,3c=1g(T,"30")=="3c";G(T.7y){J c=T.7y();1e(c.1A+29.2f(1Q.1C.2e,1Q.1c.2e),c.1S+29.2f(1Q.1C.2c,1Q.1c.2c));1e(-1Q.1C.6b,-1Q.1C.6a)}N{1e(T.5X,T.5W);1B(1s){1e(1s.5X,1s.5W);G(42&&!/^t(98|d|h)$/i.11(1s.2j)||2k&&!5U)2C(1s);G(!3c&&1g(1s,"30")=="3c")3c=M;4a=/^1c$/i.11(1s.2j)?4a:1s;1s=1s.1s}1B(d&&d.2j&&!/^1c|2K$/i.11(d.2j)){G(!/^96|1T.*$/i.11(1g(d,"18")))1e(-d.2e,-d.2c);G(42&&1g(d,"33")!="4j")2C(d);d=d.1d}G((5U&&(3c||1g(4a,"30")=="5x"))||(42&&1g(4a,"30")!="5x"))1e(-1Q.1c.5X,-1Q.1c.5W);G(3c)1e(29.2f(1Q.1C.2e,1Q.1c.2e),29.2f(1Q.1C.2c,1Q.1c.2c))}3q={1S:1S,1A:b}}H 2C(a){1e(D.2a(a,"6V",M),D.2a(a,"6U",M))}H 1e(l,t){b+=3r(l,10)||0;1S+=3r(t,10)||0}I 3q};D.17.1l({30:H(){J a=0,1S=0,3q;G(7[0]){J b=7.1s(),2i=7.2i(),4c=/^1c|2K$/i.11(b[0].2j)?{1S:0,1A:0}:b.2i();2i.1S-=25(7,\'94\');2i.1A-=25(7,\'aF\');4c.1S+=25(b,\'6U\');4c.1A+=25(b,\'6V\');3q={1S:2i.1S-4c.1S,1A:2i.1A-4c.1A}}I 3q},1s:H(){J a=7[0].1s;1B(a&&(!/^1c|2K$/i.11(a.2j)&&D.1g(a,\'30\')==\'93\'))a=a.1s;I D(a)}});D.P([\'5e\',\'5G\'],H(i,b){J c=\'4y\'+b;D.17[c]=H(a){G(!7[0])I;I a!=12?7.P(H(){7==1b||7==S?1b.92(!i?a:D(1b).2e(),i?a:D(1b).2c()):7[c]=a}):7[0]==1b||7[0]==S?46[i?\'aI\':\'aJ\']||D.71&&S.1C[c]||S.1c[c]:7[0][c]}});D.P(["6N","4b"],H(i,b){J c=i?"5e":"5G",4f=i?"6k":"6i";D.17["5s"+b]=H(){I 7[b.3y()]()+25(7,"57"+c)+25(7,"57"+4f)};D.17["90"+b]=H(a){I 7["5s"+b]()+25(7,"2C"+c+"4b")+25(7,"2C"+4f+"4b")+(a?25(7,"6S"+c)+25(7,"6S"+4f):0)}})})();',62,669,'|||||||this|||||||||||||||||||||||||||||||||||if|function|return|var|length|data|true|else|type|each|false|for|document|elem|null|style|event||nodeName|||test|undefined||browser|options|nodeType|fn|display|arguments|url|window|body|parentNode|add|msie|css|indexOf|prop|typeof|call|extend|script|in|replace|push|constructor|text|offsetParent|cur|status|div|apply|firstChild|opacity|now|left|while|documentElement|isFunction|filter|className|hidden|handle|match|complete|attr|ret|hide|show|dataType|trigger|doc|split|top|table|try|catch|success|break|cache|height||remove|tbody|string|guid|num|global|ready|fx|Math|curCSS|start|scrollTop|makeArray|scrollLeft|max|animate|width|offset|tagName|safari|map|toggle||done|Array|find|toUpperCase|button|special|duration|id|copy|value|handler|ownerDocument|select|new|border|exec|stack|none|opera|nextSibling|pushStack|target|html|inArray|unit|xml|bind|GET|isReady|merge|pos|timeout|delete|one|selected|px|step|jsre|position|async|preventDefault|overflow|name|which|queue|removeChild|namespace|insertBefore|nth|removeData|fixed|parseFloat|error|readyState|multiFilter|createElement|rl|re|trim|end|_|param|first|get|results|parseInt|slice|childNodes|encodeURIComponent|append|events|elems|toLowerCase|json|readyList|setTimeout|grep|mouseenter|color|is|custom|getElementsByTagName|block|stopPropagation|addEventListener|callee|proxy|mouseleave|timers|defaultView|password|disabled|last|has|appendChild|form|domManip|props|ajax|orig|set|easing|mozilla|load|prototype|curAnim|self|charCode|timerId|object|offsetChild|Width|parentOffset|src|unbind|br|currentStyle|clean|float|visible|relatedTarget|previousSibling|handlers|isXMLDoc|on|setup|nodeIndex|unique|shift|javascript|child|RegExp|_default|deep|scroll|lastModified|teardown|setRequestHeader|timeStamp|update|empty|tr|getAttribute|innerHTML|setInterval|checked|fromElement|Number|jQuery|state|active|jsonp|accepts|application|dir|input|responseText|click|styleSheets|unload|not|lastToggle|outline|mouseout|getPropertyValue|mouseover|getComputedStyle|bindReady|String|padding|pageX|metaKey|keyCode|getWH|andSelf|clientX|Left|all|visibility|container|index|init|triggered|removeAttribute|classFilter|prevObject|submit|file|after|windowData|inner|client|globalEval|sibling|jquery|absolute|clone|wrapAll|dequeue|version|triggerHandler|oldblock|ctrlKey|createTextNode|Top|handleError|getResponseHeader|parsererror|speeds|checkbox|old|00|radio|swing|href|Modified|ifModified|lastChild|safari2|startTime|offsetTop|offsetLeft|username|location|ajaxSettings|getElementById|isSimple|values|selectedIndex|runtimeStyle|rsLeft|_load|loaded|DOMContentLoaded|clientTop|clientLeft|toElement|srcElement|val|pageY|POST|unshift|Bottom|clientY|Right|fix|exclusive|detachEvent|cloneNode|removeEventListener|swap|toString|join|attachEvent|eval|substr|head|parse|textarea|reset|image|zoom|odd|even|before|prepend|exclude|expr|quickClass|quickID|uuid|quickChild|continue|Height|textContent|appendTo|contents|open|margin|evalScript|borderTopWidth|borderLeftWidth|parent|httpData|setArray|CSS1Compat|compatMode|boxModel|cssFloat|linear|def|webkit|nodeValue|speed|_toggle|eq|100|replaceWith|304|concat|200|alpha|Last|httpNotModified|getAttributeNode|httpSuccess|clearInterval|abort|beforeSend|splice|styleFloat|throw|colgroup|XMLHttpRequest|ActiveXObject|scriptCharset|callback|fieldset|multiple|processData|getBoundingClientRect|contentType|link|ajaxSend|ajaxSuccess|ajaxError|col|ajaxComplete|ajaxStop|ajaxStart|serializeArray|notmodified|keypress|keydown|change|mouseup|mousedown|dblclick|focus|blur|stylesheet|hasClass|rel|doScroll|black|hover|solid|cancelBubble|returnValue|wheelDelta|view|round|shiftKey|resize|screenY|screenX|relatedNode|mousemove|prevValue|originalTarget|offsetHeight|keyup|newValue|offsetWidth|eventPhase|detail|currentTarget|cancelable|bubbles|attrName|attrChange|altKey|originalEvent|charAt|0n|substring|animated|header|noConflict|line|enabled|innerText|contains|only|weight|font|gt|lt|uFFFF|u0128|size|417|Boolean|Date|toggleClass|removeClass|addClass|removeAttr|replaceAll|insertAfter|prependTo|wrap|contentWindow|contentDocument|iframe|children|siblings|prevAll|wrapInner|nextAll|outer|prev|scrollTo|static|marginTop|next|inline|parents|able|cellSpacing|adobeair|cellspacing|522|maxLength|maxlength|readOnly|400|readonly|fast|600|class|slow|1px|htmlFor|reverse|10000|PI|cos|compatible|Function|setData|ie|ra|it|rv|getData|userAgent|navigator|fadeTo|fadeIn|slideToggle|slideUp|slideDown|ig|responseXML|content|1223|NaN|fadeOut|300|protocol|send|setAttribute|option|dataFilter|cssText|changed|be|Accept|stop|With|Requested|Object|can|GMT|property|1970|Jan|01|Thu|Since|If|Type|Content|XMLHTTP|th|Microsoft|td|onreadystatechange|onload|cap|charset|colg|host|tfoot|specified|with|1_|thead|leg|plain|attributes|opt|embed|urlencoded|www|area|hr|ajaxSetup|meta|post|getJSON|getScript|marginLeft|img|elements|pageYOffset|pageXOffset|abbr|serialize|pixelLeft'.split('|'),0,{}));// $Id: drupal.js,v 1.41.2.4 2009/07/21 08:59:10 goba Exp $

var Drupal = Drupal || { 'settings': {}, 'behaviors': {}, 'themes': {}, 'locale': {} };

/**
 * Set the variable that indicates if JavaScript behaviors should be applied
 */
Drupal.jsEnabled = true;

/**
 * Attach all registered behaviors to a page element.
 *
 * Behaviors are event-triggered actions that attach to page elements, enhancing
 * default non-Javascript UIs. Behaviors are registered in the Drupal.behaviors
 * object as follows:
 * @code
 *    Drupal.behaviors.behaviorName = function () {
 *      ...
 *    };
 * @endcode
 *
 * Drupal.attachBehaviors is added below to the jQuery ready event and so
 * runs on initial page load. Developers implementing AHAH/AJAX in their
 * solutions should also call this function after new page content has been
 * loaded, feeding in an element to be processed, in order to attach all
 * behaviors to the new content.
 *
 * Behaviors should use a class in the form behaviorName-processed to ensure
 * the behavior is attached only once to a given element. (Doing so enables
 * the reprocessing of given elements, which may be needed on occasion despite
 * the ability to limit behavior attachment to a particular element.)
 *
 * @param context
 *   An element to attach behaviors to. If none is given, the document element
 *   is used.
 */
Drupal.attachBehaviors = function(context) {
  context = context || document;
  // Execute all of them.
  jQuery.each(Drupal.behaviors, function() {
    this(context);
  });
};

/**
 * Encode special characters in a plain-text string for display as HTML.
 */
Drupal.checkPlain = function(str) {
  str = String(str);
  var replace = { '&': '&amp;', '"': '&quot;', '<': '&lt;', '>': '&gt;' };
  for (var character in replace) {
    var regex = new RegExp(character, 'g');
    str = str.replace(regex, replace[character]);
  }
  return str;
};

/**
 * Translate strings to the page language or a given language.
 *
 * See the documentation of the server-side t() function for further details.
 *
 * @param str
 *   A string containing the English string to translate.
 * @param args
 *   An object of replacements pairs to make after translation. Incidences
 *   of any key in this array are replaced with the corresponding value.
 *   Based on the first character of the key, the value is escaped and/or themed:
 *    - !variable: inserted as is
 *    - @variable: escape plain text to HTML (Drupal.checkPlain)
 *    - %variable: escape text and theme as a placeholder for user-submitted
 *      content (checkPlain + Drupal.theme('placeholder'))
 * @return
 *   The translated string.
 */
Drupal.t = function(str, args) {
  // Fetch the localized version of the string.
  if (Drupal.locale.strings && Drupal.locale.strings[str]) {
    str = Drupal.locale.strings[str];
  }

  if (args) {
    // Transform arguments before inserting them
    for (var key in args) {
      switch (key.charAt(0)) {
        // Escaped only
        case '@':
          args[key] = Drupal.checkPlain(args[key]);
        break;
        // Pass-through
        case '!':
          break;
        // Escaped and placeholder
        case '%':
        default:
          args[key] = Drupal.theme('placeholder', args[key]);
          break;
      }
      str = str.replace(key, args[key]);
    }
  }
  return str;
};

/**
 * Format a string containing a count of items.
 *
 * This function ensures that the string is pluralized correctly. Since Drupal.t() is
 * called by this function, make sure not to pass already-localized strings to it.
 *
 * See the documentation of the server-side format_plural() function for further details.
 *
 * @param count
 *   The item count to display.
 * @param singular
 *   The string for the singular case. Please make sure it is clear this is
 *   singular, to ease translation (e.g. use "1 new comment" instead of "1 new").
 *   Do not use @count in the singular string.
 * @param plural
 *   The string for the plural case. Please make sure it is clear this is plural,
 *   to ease translation. Use @count in place of the item count, as in "@count
 *   new comments".
 * @param args
 *   An object of replacements pairs to make after translation. Incidences
 *   of any key in this array are replaced with the corresponding value.
 *   Based on the first character of the key, the value is escaped and/or themed:
 *    - !variable: inserted as is
 *    - @variable: escape plain text to HTML (Drupal.checkPlain)
 *    - %variable: escape text and theme as a placeholder for user-submitted
 *      content (checkPlain + Drupal.theme('placeholder'))
 *   Note that you do not need to include @count in this array.
 *   This replacement is done automatically for the plural case.
 * @return
 *   A translated string.
 */
Drupal.formatPlural = function(count, singular, plural, args) {
  var args = args || {};
  args['@count'] = count;
  // Determine the index of the plural form.
  var index = Drupal.locale.pluralFormula ? Drupal.locale.pluralFormula(args['@count']) : ((args['@count'] == 1) ? 0 : 1);

  if (index == 0) {
    return Drupal.t(singular, args);
  }
  else if (index == 1) {
    return Drupal.t(plural, args);
  }
  else {
    args['@count['+ index +']'] = args['@count'];
    delete args['@count'];
    return Drupal.t(plural.replace('@count', '@count['+ index +']'));
  }
};

/**
 * Generate the themed representation of a Drupal object.
 *
 * All requests for themed output must go through this function. It examines
 * the request and routes it to the appropriate theme function. If the current
 * theme does not provide an override function, the generic theme function is
 * called.
 *
 * For example, to retrieve the HTML that is output by theme_placeholder(text),
 * call Drupal.theme('placeholder', text).
 *
 * @param func
 *   The name of the theme function to call.
 * @param ...
 *   Additional arguments to pass along to the theme function.
 * @return
 *   Any data the theme function returns. This could be a plain HTML string,
 *   but also a complex object.
 */
Drupal.theme = function(func) {
  for (var i = 1, args = []; i < arguments.length; i++) {
    args.push(arguments[i]);
  }

  return (Drupal.theme[func] || Drupal.theme.prototype[func]).apply(this, args);
};

/**
 * Parse a JSON response.
 *
 * The result is either the JSON object, or an object with 'status' 0 and 'data' an error message.
 */
Drupal.parseJson = function (data) {
  if ((data.substring(0, 1) != '{') && (data.substring(0, 1) != '[')) {
    return { status: 0, data: data.length ? data : Drupal.t('Unspecified error') };
  }
  return eval('(' + data + ');');
};

/**
 * Freeze the current body height (as minimum height). Used to prevent
 * unnecessary upwards scrolling when doing DOM manipulations.
 */
Drupal.freezeHeight = function () {
  Drupal.unfreezeHeight();
  var div = document.createElement('div');
  $(div).css({
    position: 'absolute',
    top: '0px',
    left: '0px',
    width: '1px',
    height: $('body').css('height')
  }).attr('id', 'freeze-height');
  $('body').append(div);
};

/**
 * Unfreeze the body height
 */
Drupal.unfreezeHeight = function () {
  $('#freeze-height').remove();
};

/**
 * Wrapper around encodeURIComponent() which avoids Apache quirks (equivalent of
 * drupal_urlencode() in PHP). This function should only be used on paths, not
 * on query string arguments.
 */
Drupal.encodeURIComponent = function (item, uri) {
  uri = uri || location.href;
  item = encodeURIComponent(item).replace(/%2F/g, '/');
  return (uri.indexOf('?q=') != -1) ? item : item.replace(/%26/g, '%2526').replace(/%23/g, '%2523').replace(/\/\//g, '/%252F');
};

/**
 * Get the text selection in a textarea.
 */
Drupal.getSelection = function (element) {
  if (typeof(element.selectionStart) != 'number' && document.selection) {
    // The current selection
    var range1 = document.selection.createRange();
    var range2 = range1.duplicate();
    // Select all text.
    range2.moveToElementText(element);
    // Now move 'dummy' end point to end point of original range.
    range2.setEndPoint('EndToEnd', range1);
    // Now we can calculate start and end points.
    var start = range2.text.length - range1.text.length;
    var end = start + range1.text.length;
    return { 'start': start, 'end': end };
  }
  return { 'start': element.selectionStart, 'end': element.selectionEnd };
};

/**
 * Build an error message from ahah response.
 */
Drupal.ahahError = function(xmlhttp, uri) {
  if (xmlhttp.status == 200) {
    if (jQuery.trim(xmlhttp.responseText)) {
      var message = Drupal.t("An error occurred. \n@uri\n@text", {'@uri': uri, '@text': xmlhttp.responseText });
    }
    else {
      var message = Drupal.t("An error occurred. \n@uri\n(no information available).", {'@uri': uri });
    }
  }
  else {
    var message = Drupal.t("An HTTP error @status occurred. \n@uri", {'@uri': uri, '@status': xmlhttp.status });
  }
  return message.replace(/\n/g, '<br />');
}

// Global Killswitch on the <html> element
$(document.documentElement).addClass('js');
// Attach all behaviors.
$(document).ready(function() {
  Drupal.attachBehaviors(this);
});

/**
 * The default themes.
 */
Drupal.theme.prototype = {

  /**
   * Formats text for emphasized display in a placeholder inside a sentence.
   *
   * @param str
   *   The text to format (plain-text).
   * @return
   *   The formatted text (html).
   */
  placeholder: function(str) {
    return '<em>' + Drupal.checkPlain(str) + '</em>';
  }
};
;/*
 * jQuery JavaScript Library v1.3.2
 * http://jquery.com/
 *
 * Copyright (c) 2009 John Resig
 * Dual licensed under the MIT and GPL licenses.
 * http://docs.jquery.com/License
 *
 * Date: 2009-02-19 17:34:21 -0500 (Thu, 19 Feb 2009)
 * Revision: 6246
 */
(function(){var l=this,g,y=l.jQuery,p=l.$,o=l.jQuery=l.$=function(E,F){return new o.fn.init(E,F)},D=/^[^<]*(<(.|\s)+>)[^>]*$|^#([\w-]+)$/,f=/^.[^:#\[\.,]*$/;o.fn=o.prototype={init:function(E,H){E=E||document;if(E.nodeType){this[0]=E;this.length=1;this.context=E;return this}if(typeof E==="string"){var G=D.exec(E);if(G&&(G[1]||!H)){if(G[1]){E=o.clean([G[1]],H)}else{var I=document.getElementById(G[3]);if(I&&I.id!=G[3]){return o().find(E)}var F=o(I||[]);F.context=document;F.selector=E;return F}}else{return o(H).find(E)}}else{if(o.isFunction(E)){return o(document).ready(E)}}if(E.selector&&E.context){this.selector=E.selector;this.context=E.context}return this.setArray(o.isArray(E)?E:o.makeArray(E))},selector:"",jquery:"1.3.2",size:function(){return this.length},get:function(E){return E===g?Array.prototype.slice.call(this):this[E]},pushStack:function(F,H,E){var G=o(F);G.prevObject=this;G.context=this.context;if(H==="find"){G.selector=this.selector+(this.selector?" ":"")+E}else{if(H){G.selector=this.selector+"."+H+"("+E+")"}}return G},setArray:function(E){this.length=0;Array.prototype.push.apply(this,E);return this},each:function(F,E){return o.each(this,F,E)},index:function(E){return o.inArray(E&&E.jquery?E[0]:E,this)},attr:function(F,H,G){var E=F;if(typeof F==="string"){if(H===g){return this[0]&&o[G||"attr"](this[0],F)}else{E={};E[F]=H}}return this.each(function(I){for(F in E){o.attr(G?this.style:this,F,o.prop(this,E[F],G,I,F))}})},css:function(E,F){if((E=="width"||E=="height")&&parseFloat(F)<0){F=g}return this.attr(E,F,"curCSS")},text:function(F){if(typeof F!=="object"&&F!=null){return this.empty().append((this[0]&&this[0].ownerDocument||document).createTextNode(F))}var E="";o.each(F||this,function(){o.each(this.childNodes,function(){if(this.nodeType!=8){E+=this.nodeType!=1?this.nodeValue:o.fn.text([this])}})});return E},wrapAll:function(E){if(this[0]){var F=o(E,this[0].ownerDocument).clone();if(this[0].parentNode){F.insertBefore(this[0])}F.map(function(){var G=this;while(G.firstChild){G=G.firstChild}return G}).append(this)}return this},wrapInner:function(E){return this.each(function(){o(this).contents().wrapAll(E)})},wrap:function(E){return this.each(function(){o(this).wrapAll(E)})},append:function(){return this.domManip(arguments,true,function(E){if(this.nodeType==1){this.appendChild(E)}})},prepend:function(){return this.domManip(arguments,true,function(E){if(this.nodeType==1){this.insertBefore(E,this.firstChild)}})},before:function(){return this.domManip(arguments,false,function(E){this.parentNode.insertBefore(E,this)})},after:function(){return this.domManip(arguments,false,function(E){this.parentNode.insertBefore(E,this.nextSibling)})},end:function(){return this.prevObject||o([])},push:[].push,sort:[].sort,splice:[].splice,find:function(E){if(this.length===1){var F=this.pushStack([],"find",E);F.length=0;o.find(E,this[0],F);return F}else{return this.pushStack(o.unique(o.map(this,function(G){return o.find(E,G)})),"find",E)}},clone:function(G){var E=this.map(function(){if(!o.support.noCloneEvent&&!o.isXMLDoc(this)){var I=this.outerHTML;if(!I){var J=this.ownerDocument.createElement("div");J.appendChild(this.cloneNode(true));I=J.innerHTML}return o.clean([I.replace(/ jQuery\d+="(?:\d+|null)"/g,"").replace(/^\s*/,"")])[0]}else{return this.cloneNode(true)}});if(G===true){var H=this.find("*").andSelf(),F=0;E.find("*").andSelf().each(function(){if(this.nodeName!==H[F].nodeName){return}var I=o.data(H[F],"events");for(var K in I){for(var J in I[K]){o.event.add(this,K,I[K][J],I[K][J].data)}}F++})}return E},filter:function(E){return this.pushStack(o.isFunction(E)&&o.grep(this,function(G,F){return E.call(G,F)})||o.multiFilter(E,o.grep(this,function(F){return F.nodeType===1})),"filter",E)},closest:function(E){var G=o.expr.match.POS.test(E)?o(E):null,F=0;return this.map(function(){var H=this;while(H&&H.ownerDocument){if(G?G.index(H)>-1:o(H).is(E)){o.data(H,"closest",F);return H}H=H.parentNode;F++}})},not:function(E){if(typeof E==="string"){if(f.test(E)){return this.pushStack(o.multiFilter(E,this,true),"not",E)}else{E=o.multiFilter(E,this)}}var F=E.length&&E[E.length-1]!==g&&!E.nodeType;return this.filter(function(){return F?o.inArray(this,E)<0:this!=E})},add:function(E){return this.pushStack(o.unique(o.merge(this.get(),typeof E==="string"?o(E):o.makeArray(E))))},is:function(E){return !!E&&o.multiFilter(E,this).length>0},hasClass:function(E){return !!E&&this.is("."+E)},val:function(K){if(K===g){var E=this[0];if(E){if(o.nodeName(E,"option")){return(E.attributes.value||{}).specified?E.value:E.text}if(o.nodeName(E,"select")){var I=E.selectedIndex,L=[],M=E.options,H=E.type=="select-one";if(I<0){return null}for(var F=H?I:0,J=H?I+1:M.length;F<J;F++){var G=M[F];if(G.selected){K=o(G).val();if(H){return K}L.push(K)}}return L}return(E.value||"").replace(/\r/g,"")}return g}if(typeof K==="number"){K+=""}return this.each(function(){if(this.nodeType!=1){return}if(o.isArray(K)&&/radio|checkbox/.test(this.type)){this.checked=(o.inArray(this.value,K)>=0||o.inArray(this.name,K)>=0)}else{if(o.nodeName(this,"select")){var N=o.makeArray(K);o("option",this).each(function(){this.selected=(o.inArray(this.value,N)>=0||o.inArray(this.text,N)>=0)});if(!N.length){this.selectedIndex=-1}}else{this.value=K}}})},html:function(E){return E===g?(this[0]?this[0].innerHTML.replace(/ jQuery\d+="(?:\d+|null)"/g,""):null):this.empty().append(E)},replaceWith:function(E){return this.after(E).remove()},eq:function(E){return this.slice(E,+E+1)},slice:function(){return this.pushStack(Array.prototype.slice.apply(this,arguments),"slice",Array.prototype.slice.call(arguments).join(","))},map:function(E){return this.pushStack(o.map(this,function(G,F){return E.call(G,F,G)}))},andSelf:function(){return this.add(this.prevObject)},domManip:function(J,M,L){if(this[0]){var I=(this[0].ownerDocument||this[0]).createDocumentFragment(),F=o.clean(J,(this[0].ownerDocument||this[0]),I),H=I.firstChild;if(H){for(var G=0,E=this.length;G<E;G++){L.call(K(this[G],H),this.length>1||G>0?I.cloneNode(true):I)}}if(F){o.each(F,z)}}return this;function K(N,O){return M&&o.nodeName(N,"table")&&o.nodeName(O,"tr")?(N.getElementsByTagName("tbody")[0]||N.appendChild(N.ownerDocument.createElement("tbody"))):N}}};o.fn.init.prototype=o.fn;function z(E,F){if(F.src){o.ajax({url:F.src,async:false,dataType:"script"})}else{o.globalEval(F.text||F.textContent||F.innerHTML||"")}if(F.parentNode){F.parentNode.removeChild(F)}}function e(){return +new Date}o.extend=o.fn.extend=function(){var J=arguments[0]||{},H=1,I=arguments.length,E=false,G;if(typeof J==="boolean"){E=J;J=arguments[1]||{};H=2}if(typeof J!=="object"&&!o.isFunction(J)){J={}}if(I==H){J=this;--H}for(;H<I;H++){if((G=arguments[H])!=null){for(var F in G){var K=J[F],L=G[F];if(J===L){continue}if(E&&L&&typeof L==="object"&&!L.nodeType){J[F]=o.extend(E,K||(L.length!=null?[]:{}),L)}else{if(L!==g){J[F]=L}}}}}return J};var b=/z-?index|font-?weight|opacity|zoom|line-?height/i,q=document.defaultView||{},s=Object.prototype.toString;o.extend({noConflict:function(E){l.$=p;if(E){l.jQuery=y}return o},isFunction:function(E){return s.call(E)==="[object Function]"},isArray:function(E){return s.call(E)==="[object Array]"},isXMLDoc:function(E){return E.nodeType===9&&E.documentElement.nodeName!=="HTML"||!!E.ownerDocument&&o.isXMLDoc(E.ownerDocument)},globalEval:function(G){if(G&&/\S/.test(G)){var F=document.getElementsByTagName("head")[0]||document.documentElement,E=document.createElement("script");E.type="text/javascript";if(o.support.scriptEval){E.appendChild(document.createTextNode(G))}else{E.text=G}F.insertBefore(E,F.firstChild);F.removeChild(E)}},nodeName:function(F,E){return F.nodeName&&F.nodeName.toUpperCase()==E.toUpperCase()},each:function(G,K,F){var E,H=0,I=G.length;if(F){if(I===g){for(E in G){if(K.apply(G[E],F)===false){break}}}else{for(;H<I;){if(K.apply(G[H++],F)===false){break}}}}else{if(I===g){for(E in G){if(K.call(G[E],E,G[E])===false){break}}}else{for(var J=G[0];H<I&&K.call(J,H,J)!==false;J=G[++H]){}}}return G},prop:function(H,I,G,F,E){if(o.isFunction(I)){I=I.call(H,F)}return typeof I==="number"&&G=="curCSS"&&!b.test(E)?I+"px":I},className:{add:function(E,F){o.each((F||"").split(/\s+/),function(G,H){if(E.nodeType==1&&!o.className.has(E.className,H)){E.className+=(E.className?" ":"")+H}})},remove:function(E,F){if(E.nodeType==1){E.className=F!==g?o.grep(E.className.split(/\s+/),function(G){return !o.className.has(F,G)}).join(" "):""}},has:function(F,E){return F&&o.inArray(E,(F.className||F).toString().split(/\s+/))>-1}},swap:function(H,G,I){var E={};for(var F in G){E[F]=H.style[F];H.style[F]=G[F]}I.call(H);for(var F in G){H.style[F]=E[F]}},css:function(H,F,J,E){if(F=="width"||F=="height"){var L,G={position:"absolute",visibility:"hidden",display:"block"},K=F=="width"?["Left","Right"]:["Top","Bottom"];function I(){L=F=="width"?H.offsetWidth:H.offsetHeight;if(E==="border"){return}o.each(K,function(){if(!E){L-=parseFloat(o.curCSS(H,"padding"+this,true))||0}if(E==="margin"){L+=parseFloat(o.curCSS(H,"margin"+this,true))||0}else{L-=parseFloat(o.curCSS(H,"border"+this+"Width",true))||0}})}if(H.offsetWidth!==0){I()}else{o.swap(H,G,I)}return Math.max(0,Math.round(L))}return o.curCSS(H,F,J)},curCSS:function(I,F,G){var L,E=I.style;if(F=="opacity"&&!o.support.opacity){L=o.attr(E,"opacity");return L==""?"1":L}if(F.match(/float/i)){F=w}if(!G&&E&&E[F]){L=E[F]}else{if(q.getComputedStyle){if(F.match(/float/i)){F="float"}F=F.replace(/([A-Z])/g,"-$1").toLowerCase();var M=q.getComputedStyle(I,null);if(M){L=M.getPropertyValue(F)}if(F=="opacity"&&L==""){L="1"}}else{if(I.currentStyle){var J=F.replace(/\-(\w)/g,function(N,O){return O.toUpperCase()});L=I.currentStyle[F]||I.currentStyle[J];if(!/^\d+(px)?$/i.test(L)&&/^\d/.test(L)){var H=E.left,K=I.runtimeStyle.left;I.runtimeStyle.left=I.currentStyle.left;E.left=L||0;L=E.pixelLeft+"px";E.left=H;I.runtimeStyle.left=K}}}}return L},clean:function(F,K,I){K=K||document;if(typeof K.createElement==="undefined"){K=K.ownerDocument||K[0]&&K[0].ownerDocument||document}if(!I&&F.length===1&&typeof F[0]==="string"){var H=/^<(\w+)\s*\/?>$/.exec(F[0]);if(H){return[K.createElement(H[1])]}}var G=[],E=[],L=K.createElement("div");o.each(F,function(P,S){if(typeof S==="number"){S+=""}if(!S){return}if(typeof S==="string"){S=S.replace(/(<(\w+)[^>]*?)\/>/g,function(U,V,T){return T.match(/^(abbr|br|col|img|input|link|meta|param|hr|area|embed)$/i)?U:V+"></"+T+">"});var O=S.replace(/^\s+/,"").substring(0,10).toLowerCase();var Q=!O.indexOf("<opt")&&[1,"<select multiple='multiple'>","</select>"]||!O.indexOf("<leg")&&[1,"<fieldset>","</fieldset>"]||O.match(/^<(thead|tbody|tfoot|colg|cap)/)&&[1,"<table>","</table>"]||!O.indexOf("<tr")&&[2,"<table><tbody>","</tbody></table>"]||(!O.indexOf("<td")||!O.indexOf("<th"))&&[3,"<table><tbody><tr>","</tr></tbody></table>"]||!O.indexOf("<col")&&[2,"<table><tbody></tbody><colgroup>","</colgroup></table>"]||!o.support.htmlSerialize&&[1,"div<div>","</div>"]||[0,"",""];L.innerHTML=Q[1]+S+Q[2];while(Q[0]--){L=L.lastChild}if(!o.support.tbody){var R=/<tbody/i.test(S),N=!O.indexOf("<table")&&!R?L.firstChild&&L.firstChild.childNodes:Q[1]=="<table>"&&!R?L.childNodes:[];for(var M=N.length-1;M>=0;--M){if(o.nodeName(N[M],"tbody")&&!N[M].childNodes.length){N[M].parentNode.removeChild(N[M])}}}if(!o.support.leadingWhitespace&&/^\s/.test(S)){L.insertBefore(K.createTextNode(S.match(/^\s*/)[0]),L.firstChild)}S=o.makeArray(L.childNodes)}if(S.nodeType){G.push(S)}else{G=o.merge(G,S)}});if(I){for(var J=0;G[J];J++){if(o.nodeName(G[J],"script")&&(!G[J].type||G[J].type.toLowerCase()==="text/javascript")){E.push(G[J].parentNode?G[J].parentNode.removeChild(G[J]):G[J])}else{if(G[J].nodeType===1){G.splice.apply(G,[J+1,0].concat(o.makeArray(G[J].getElementsByTagName("script"))))}I.appendChild(G[J])}}return E}return G},attr:function(J,G,K){if(!J||J.nodeType==3||J.nodeType==8){return g}var H=!o.isXMLDoc(J),L=K!==g;G=H&&o.props[G]||G;if(J.tagName){var F=/href|src|style/.test(G);if(G=="selected"&&J.parentNode){J.parentNode.selectedIndex}if(G in J&&H&&!F){if(L){if(G=="type"&&o.nodeName(J,"input")&&J.parentNode){throw"type property can't be changed"}J[G]=K}if(o.nodeName(J,"form")&&J.getAttributeNode(G)){return J.getAttributeNode(G).nodeValue}if(G=="tabIndex"){var I=J.getAttributeNode("tabIndex");return I&&I.specified?I.value:J.nodeName.match(/(button|input|object|select|textarea)/i)?0:J.nodeName.match(/^(a|area)$/i)&&J.href?0:g}return J[G]}if(!o.support.style&&H&&G=="style"){return o.attr(J.style,"cssText",K)}if(L){J.setAttribute(G,""+K)}var E=!o.support.hrefNormalized&&H&&F?J.getAttribute(G,2):J.getAttribute(G);return E===null?g:E}if(!o.support.opacity&&G=="opacity"){if(L){J.zoom=1;J.filter=(J.filter||"").replace(/alpha\([^)]*\)/,"")+(parseInt(K)+""=="NaN"?"":"alpha(opacity="+K*100+")")}return J.filter&&J.filter.indexOf("opacity=")>=0?(parseFloat(J.filter.match(/opacity=([^)]*)/)[1])/100)+"":""}G=G.replace(/-([a-z])/ig,function(M,N){return N.toUpperCase()});if(L){J[G]=K}return J[G]},trim:function(E){return(E||"").replace(/^\s+|\s+$/g,"")},makeArray:function(G){var E=[];if(G!=null){var F=G.length;if(F==null||typeof G==="string"||o.isFunction(G)||G.setInterval){E[0]=G}else{while(F){E[--F]=G[F]}}}return E},inArray:function(G,H){for(var E=0,F=H.length;E<F;E++){if(H[E]===G){return E}}return -1},merge:function(H,E){var F=0,G,I=H.length;if(!o.support.getAll){while((G=E[F++])!=null){if(G.nodeType!=8){H[I++]=G}}}else{while((G=E[F++])!=null){H[I++]=G}}return H},unique:function(K){var F=[],E={};try{for(var G=0,H=K.length;G<H;G++){var J=o.data(K[G]);if(!E[J]){E[J]=true;F.push(K[G])}}}catch(I){F=K}return F},grep:function(F,J,E){var G=[];for(var H=0,I=F.length;H<I;H++){if(!E!=!J(F[H],H)){G.push(F[H])}}return G},map:function(E,J){var F=[];for(var G=0,H=E.length;G<H;G++){var I=J(E[G],G);if(I!=null){F[F.length]=I}}return F.concat.apply([],F)}});var C=navigator.userAgent.toLowerCase();o.browser={version:(C.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/)||[0,"0"])[1],safari:/webkit/.test(C),opera:/opera/.test(C),msie:/msie/.test(C)&&!/opera/.test(C),mozilla:/mozilla/.test(C)&&!/(compatible|webkit)/.test(C)};o.each({parent:function(E){return E.parentNode},parents:function(E){return o.dir(E,"parentNode")},next:function(E){return o.nth(E,2,"nextSibling")},prev:function(E){return o.nth(E,2,"previousSibling")},nextAll:function(E){return o.dir(E,"nextSibling")},prevAll:function(E){return o.dir(E,"previousSibling")},siblings:function(E){return o.sibling(E.parentNode.firstChild,E)},children:function(E){return o.sibling(E.firstChild)},contents:function(E){return o.nodeName(E,"iframe")?E.contentDocument||E.contentWindow.document:o.makeArray(E.childNodes)}},function(E,F){o.fn[E]=function(G){var H=o.map(this,F);if(G&&typeof G=="string"){H=o.multiFilter(G,H)}return this.pushStack(o.unique(H),E,G)}});o.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(E,F){o.fn[E]=function(G){var J=[],L=o(G);for(var K=0,H=L.length;K<H;K++){var I=(K>0?this.clone(true):this).get();o.fn[F].apply(o(L[K]),I);J=J.concat(I)}return this.pushStack(J,E,G)}});o.each({removeAttr:function(E){o.attr(this,E,"");if(this.nodeType==1){this.removeAttribute(E)}},addClass:function(E){o.className.add(this,E)},removeClass:function(E){o.className.remove(this,E)},toggleClass:function(F,E){if(typeof E!=="boolean"){E=!o.className.has(this,F)}o.className[E?"add":"remove"](this,F)},remove:function(E){if(!E||o.filter(E,[this]).length){o("*",this).add([this]).each(function(){o.event.remove(this);o.removeData(this)});if(this.parentNode){this.parentNode.removeChild(this)}}},empty:function(){o(this).children().remove();while(this.firstChild){this.removeChild(this.firstChild)}}},function(E,F){o.fn[E]=function(){return this.each(F,arguments)}});function j(E,F){return E[0]&&parseInt(o.curCSS(E[0],F,true),10)||0}var h="jQuery"+e(),v=0,A={};o.extend({cache:{},data:function(F,E,G){F=F==l?A:F;var H=F[h];if(!H){H=F[h]=++v}if(E&&!o.cache[H]){o.cache[H]={}}if(G!==g){o.cache[H][E]=G}return E?o.cache[H][E]:H},removeData:function(F,E){F=F==l?A:F;var H=F[h];if(E){if(o.cache[H]){delete o.cache[H][E];E="";for(E in o.cache[H]){break}if(!E){o.removeData(F)}}}else{try{delete F[h]}catch(G){if(F.removeAttribute){F.removeAttribute(h)}}delete o.cache[H]}},queue:function(F,E,H){if(F){E=(E||"fx")+"queue";var G=o.data(F,E);if(!G||o.isArray(H)){G=o.data(F,E,o.makeArray(H))}else{if(H){G.push(H)}}}return G},dequeue:function(H,G){var E=o.queue(H,G),F=E.shift();if(!G||G==="fx"){F=E[0]}if(F!==g){F.call(H)}}});o.fn.extend({data:function(E,G){var H=E.split(".");H[1]=H[1]?"."+H[1]:"";if(G===g){var F=this.triggerHandler("getData"+H[1]+"!",[H[0]]);if(F===g&&this.length){F=o.data(this[0],E)}return F===g&&H[1]?this.data(H[0]):F}else{return this.trigger("setData"+H[1]+"!",[H[0],G]).each(function(){o.data(this,E,G)})}},removeData:function(E){return this.each(function(){o.removeData(this,E)})},queue:function(E,F){if(typeof E!=="string"){F=E;E="fx"}if(F===g){return o.queue(this[0],E)}return this.each(function(){var G=o.queue(this,E,F);if(E=="fx"&&G.length==1){G[0].call(this)}})},dequeue:function(E){return this.each(function(){o.dequeue(this,E)})}});
/*
 * Sizzle CSS Selector Engine - v0.9.3
 *  Copyright 2009, The Dojo Foundation
 *  Released under the MIT, BSD, and GPL Licenses.
 *  More information: http://sizzlejs.com/
 */
(function(){var R=/((?:\((?:\([^()]+\)|[^()]+)+\)|\[(?:\[[^[\]]*\]|['"][^'"]*['"]|[^[\]'"]+)+\]|\\.|[^ >+~,(\[\\]+)+|[>+~])(\s*,\s*)?/g,L=0,H=Object.prototype.toString;var F=function(Y,U,ab,ac){ab=ab||[];U=U||document;if(U.nodeType!==1&&U.nodeType!==9){return[]}if(!Y||typeof Y!=="string"){return ab}var Z=[],W,af,ai,T,ad,V,X=true;R.lastIndex=0;while((W=R.exec(Y))!==null){Z.push(W[1]);if(W[2]){V=RegExp.rightContext;break}}if(Z.length>1&&M.exec(Y)){if(Z.length===2&&I.relative[Z[0]]){af=J(Z[0]+Z[1],U)}else{af=I.relative[Z[0]]?[U]:F(Z.shift(),U);while(Z.length){Y=Z.shift();if(I.relative[Y]){Y+=Z.shift()}af=J(Y,af)}}}else{var ae=ac?{expr:Z.pop(),set:E(ac)}:F.find(Z.pop(),Z.length===1&&U.parentNode?U.parentNode:U,Q(U));af=F.filter(ae.expr,ae.set);if(Z.length>0){ai=E(af)}else{X=false}while(Z.length){var ah=Z.pop(),ag=ah;if(!I.relative[ah]){ah=""}else{ag=Z.pop()}if(ag==null){ag=U}I.relative[ah](ai,ag,Q(U))}}if(!ai){ai=af}if(!ai){throw"Syntax error, unrecognized expression: "+(ah||Y)}if(H.call(ai)==="[object Array]"){if(!X){ab.push.apply(ab,ai)}else{if(U.nodeType===1){for(var aa=0;ai[aa]!=null;aa++){if(ai[aa]&&(ai[aa]===true||ai[aa].nodeType===1&&K(U,ai[aa]))){ab.push(af[aa])}}}else{for(var aa=0;ai[aa]!=null;aa++){if(ai[aa]&&ai[aa].nodeType===1){ab.push(af[aa])}}}}}else{E(ai,ab)}if(V){F(V,U,ab,ac);if(G){hasDuplicate=false;ab.sort(G);if(hasDuplicate){for(var aa=1;aa<ab.length;aa++){if(ab[aa]===ab[aa-1]){ab.splice(aa--,1)}}}}}return ab};F.matches=function(T,U){return F(T,null,null,U)};F.find=function(aa,T,ab){var Z,X;if(!aa){return[]}for(var W=0,V=I.order.length;W<V;W++){var Y=I.order[W],X;if((X=I.match[Y].exec(aa))){var U=RegExp.leftContext;if(U.substr(U.length-1)!=="\\"){X[1]=(X[1]||"").replace(/\\/g,"");Z=I.find[Y](X,T,ab);if(Z!=null){aa=aa.replace(I.match[Y],"");break}}}}if(!Z){Z=T.getElementsByTagName("*")}return{set:Z,expr:aa}};F.filter=function(ad,ac,ag,W){var V=ad,ai=[],aa=ac,Y,T,Z=ac&&ac[0]&&Q(ac[0]);while(ad&&ac.length){for(var ab in I.filter){if((Y=I.match[ab].exec(ad))!=null){var U=I.filter[ab],ah,af;T=false;if(aa==ai){ai=[]}if(I.preFilter[ab]){Y=I.preFilter[ab](Y,aa,ag,ai,W,Z);if(!Y){T=ah=true}else{if(Y===true){continue}}}if(Y){for(var X=0;(af=aa[X])!=null;X++){if(af){ah=U(af,Y,X,aa);var ae=W^!!ah;if(ag&&ah!=null){if(ae){T=true}else{aa[X]=false}}else{if(ae){ai.push(af);T=true}}}}}if(ah!==g){if(!ag){aa=ai}ad=ad.replace(I.match[ab],"");if(!T){return[]}break}}}if(ad==V){if(T==null){throw"Syntax error, unrecognized expression: "+ad}else{break}}V=ad}return aa};var I=F.selectors={order:["ID","NAME","TAG"],match:{ID:/#((?:[\w\u00c0-\uFFFF_-]|\\.)+)/,CLASS:/\.((?:[\w\u00c0-\uFFFF_-]|\\.)+)/,NAME:/\[name=['"]*((?:[\w\u00c0-\uFFFF_-]|\\.)+)['"]*\]/,ATTR:/\[\s*((?:[\w\u00c0-\uFFFF_-]|\\.)+)\s*(?:(\S?=)\s*(['"]*)(.*?)\3|)\s*\]/,TAG:/^((?:[\w\u00c0-\uFFFF\*_-]|\\.)+)/,CHILD:/:(only|nth|last|first)-child(?:\((even|odd|[\dn+-]*)\))?/,POS:/:(nth|eq|gt|lt|first|last|even|odd)(?:\((\d*)\))?(?=[^-]|$)/,PSEUDO:/:((?:[\w\u00c0-\uFFFF_-]|\\.)+)(?:\((['"]*)((?:\([^\)]+\)|[^\2\(\)]*)+)\2\))?/},attrMap:{"class":"className","for":"htmlFor"},attrHandle:{href:function(T){return T.getAttribute("href")}},relative:{"+":function(aa,T,Z){var X=typeof T==="string",ab=X&&!/\W/.test(T),Y=X&&!ab;if(ab&&!Z){T=T.toUpperCase()}for(var W=0,V=aa.length,U;W<V;W++){if((U=aa[W])){while((U=U.previousSibling)&&U.nodeType!==1){}aa[W]=Y||U&&U.nodeName===T?U||false:U===T}}if(Y){F.filter(T,aa,true)}},">":function(Z,U,aa){var X=typeof U==="string";if(X&&!/\W/.test(U)){U=aa?U:U.toUpperCase();for(var V=0,T=Z.length;V<T;V++){var Y=Z[V];if(Y){var W=Y.parentNode;Z[V]=W.nodeName===U?W:false}}}else{for(var V=0,T=Z.length;V<T;V++){var Y=Z[V];if(Y){Z[V]=X?Y.parentNode:Y.parentNode===U}}if(X){F.filter(U,Z,true)}}},"":function(W,U,Y){var V=L++,T=S;if(!U.match(/\W/)){var X=U=Y?U:U.toUpperCase();T=P}T("parentNode",U,V,W,X,Y)},"~":function(W,U,Y){var V=L++,T=S;if(typeof U==="string"&&!U.match(/\W/)){var X=U=Y?U:U.toUpperCase();T=P}T("previousSibling",U,V,W,X,Y)}},find:{ID:function(U,V,W){if(typeof V.getElementById!=="undefined"&&!W){var T=V.getElementById(U[1]);return T?[T]:[]}},NAME:function(V,Y,Z){if(typeof Y.getElementsByName!=="undefined"){var U=[],X=Y.getElementsByName(V[1]);for(var W=0,T=X.length;W<T;W++){if(X[W].getAttribute("name")===V[1]){U.push(X[W])}}return U.length===0?null:U}},TAG:function(T,U){return U.getElementsByTagName(T[1])}},preFilter:{CLASS:function(W,U,V,T,Z,aa){W=" "+W[1].replace(/\\/g,"")+" ";if(aa){return W}for(var X=0,Y;(Y=U[X])!=null;X++){if(Y){if(Z^(Y.className&&(" "+Y.className+" ").indexOf(W)>=0)){if(!V){T.push(Y)}}else{if(V){U[X]=false}}}}return false},ID:function(T){return T[1].replace(/\\/g,"")},TAG:function(U,T){for(var V=0;T[V]===false;V++){}return T[V]&&Q(T[V])?U[1]:U[1].toUpperCase()},CHILD:function(T){if(T[1]=="nth"){var U=/(-?)(\d*)n((?:\+|-)?\d*)/.exec(T[2]=="even"&&"2n"||T[2]=="odd"&&"2n+1"||!/\D/.test(T[2])&&"0n+"+T[2]||T[2]);T[2]=(U[1]+(U[2]||1))-0;T[3]=U[3]-0}T[0]=L++;return T},ATTR:function(X,U,V,T,Y,Z){var W=X[1].replace(/\\/g,"");if(!Z&&I.attrMap[W]){X[1]=I.attrMap[W]}if(X[2]==="~="){X[4]=" "+X[4]+" "}return X},PSEUDO:function(X,U,V,T,Y){if(X[1]==="not"){if(X[3].match(R).length>1||/^\w/.test(X[3])){X[3]=F(X[3],null,null,U)}else{var W=F.filter(X[3],U,V,true^Y);if(!V){T.push.apply(T,W)}return false}}else{if(I.match.POS.test(X[0])||I.match.CHILD.test(X[0])){return true}}return X},POS:function(T){T.unshift(true);return T}},filters:{enabled:function(T){return T.disabled===false&&T.type!=="hidden"},disabled:function(T){return T.disabled===true},checked:function(T){return T.checked===true},selected:function(T){T.parentNode.selectedIndex;return T.selected===true},parent:function(T){return !!T.firstChild},empty:function(T){return !T.firstChild},has:function(V,U,T){return !!F(T[3],V).length},header:function(T){return/h\d/i.test(T.nodeName)},text:function(T){return"text"===T.type},radio:function(T){return"radio"===T.type},checkbox:function(T){return"checkbox"===T.type},file:function(T){return"file"===T.type},password:function(T){return"password"===T.type},submit:function(T){return"submit"===T.type},image:function(T){return"image"===T.type},reset:function(T){return"reset"===T.type},button:function(T){return"button"===T.type||T.nodeName.toUpperCase()==="BUTTON"},input:function(T){return/input|select|textarea|button/i.test(T.nodeName)}},setFilters:{first:function(U,T){return T===0},last:function(V,U,T,W){return U===W.length-1},even:function(U,T){return T%2===0},odd:function(U,T){return T%2===1},lt:function(V,U,T){return U<T[3]-0},gt:function(V,U,T){return U>T[3]-0},nth:function(V,U,T){return T[3]-0==U},eq:function(V,U,T){return T[3]-0==U}},filter:{PSEUDO:function(Z,V,W,aa){var U=V[1],X=I.filters[U];if(X){return X(Z,W,V,aa)}else{if(U==="contains"){return(Z.textContent||Z.innerText||"").indexOf(V[3])>=0}else{if(U==="not"){var Y=V[3];for(var W=0,T=Y.length;W<T;W++){if(Y[W]===Z){return false}}return true}}}},CHILD:function(T,W){var Z=W[1],U=T;switch(Z){case"only":case"first":while(U=U.previousSibling){if(U.nodeType===1){return false}}if(Z=="first"){return true}U=T;case"last":while(U=U.nextSibling){if(U.nodeType===1){return false}}return true;case"nth":var V=W[2],ac=W[3];if(V==1&&ac==0){return true}var Y=W[0],ab=T.parentNode;if(ab&&(ab.sizcache!==Y||!T.nodeIndex)){var X=0;for(U=ab.firstChild;U;U=U.nextSibling){if(U.nodeType===1){U.nodeIndex=++X}}ab.sizcache=Y}var aa=T.nodeIndex-ac;if(V==0){return aa==0}else{return(aa%V==0&&aa/V>=0)}}},ID:function(U,T){return U.nodeType===1&&U.getAttribute("id")===T},TAG:function(U,T){return(T==="*"&&U.nodeType===1)||U.nodeName===T},CLASS:function(U,T){return(" "+(U.className||U.getAttribute("class"))+" ").indexOf(T)>-1},ATTR:function(Y,W){var V=W[1],T=I.attrHandle[V]?I.attrHandle[V](Y):Y[V]!=null?Y[V]:Y.getAttribute(V),Z=T+"",X=W[2],U=W[4];return T==null?X==="!=":X==="="?Z===U:X==="*="?Z.indexOf(U)>=0:X==="~="?(" "+Z+" ").indexOf(U)>=0:!U?Z&&T!==false:X==="!="?Z!=U:X==="^="?Z.indexOf(U)===0:X==="$="?Z.substr(Z.length-U.length)===U:X==="|="?Z===U||Z.substr(0,U.length+1)===U+"-":false},POS:function(X,U,V,Y){var T=U[2],W=I.setFilters[T];if(W){return W(X,V,U,Y)}}}};var M=I.match.POS;for(var O in I.match){I.match[O]=RegExp(I.match[O].source+/(?![^\[]*\])(?![^\(]*\))/.source)}var E=function(U,T){U=Array.prototype.slice.call(U);if(T){T.push.apply(T,U);return T}return U};try{Array.prototype.slice.call(document.documentElement.childNodes)}catch(N){E=function(X,W){var U=W||[];if(H.call(X)==="[object Array]"){Array.prototype.push.apply(U,X)}else{if(typeof X.length==="number"){for(var V=0,T=X.length;V<T;V++){U.push(X[V])}}else{for(var V=0;X[V];V++){U.push(X[V])}}}return U}}var G;if(document.documentElement.compareDocumentPosition){G=function(U,T){var V=U.compareDocumentPosition(T)&4?-1:U===T?0:1;if(V===0){hasDuplicate=true}return V}}else{if("sourceIndex" in document.documentElement){G=function(U,T){var V=U.sourceIndex-T.sourceIndex;if(V===0){hasDuplicate=true}return V}}else{if(document.createRange){G=function(W,U){var V=W.ownerDocument.createRange(),T=U.ownerDocument.createRange();V.selectNode(W);V.collapse(true);T.selectNode(U);T.collapse(true);var X=V.compareBoundaryPoints(Range.START_TO_END,T);if(X===0){hasDuplicate=true}return X}}}}(function(){var U=document.createElement("form"),V="script"+(new Date).getTime();U.innerHTML="<input name='"+V+"'/>";var T=document.documentElement;T.insertBefore(U,T.firstChild);if(!!document.getElementById(V)){I.find.ID=function(X,Y,Z){if(typeof Y.getElementById!=="undefined"&&!Z){var W=Y.getElementById(X[1]);return W?W.id===X[1]||typeof W.getAttributeNode!=="undefined"&&W.getAttributeNode("id").nodeValue===X[1]?[W]:g:[]}};I.filter.ID=function(Y,W){var X=typeof Y.getAttributeNode!=="undefined"&&Y.getAttributeNode("id");return Y.nodeType===1&&X&&X.nodeValue===W}}T.removeChild(U)})();(function(){var T=document.createElement("div");T.appendChild(document.createComment(""));if(T.getElementsByTagName("*").length>0){I.find.TAG=function(U,Y){var X=Y.getElementsByTagName(U[1]);if(U[1]==="*"){var W=[];for(var V=0;X[V];V++){if(X[V].nodeType===1){W.push(X[V])}}X=W}return X}}T.innerHTML="<a href='#'></a>";if(T.firstChild&&typeof T.firstChild.getAttribute!=="undefined"&&T.firstChild.getAttribute("href")!=="#"){I.attrHandle.href=function(U){return U.getAttribute("href",2)}}})();if(document.querySelectorAll){(function(){var T=F,U=document.createElement("div");U.innerHTML="<p class='TEST'></p>";if(U.querySelectorAll&&U.querySelectorAll(".TEST").length===0){return}F=function(Y,X,V,W){X=X||document;if(!W&&X.nodeType===9&&!Q(X)){try{return E(X.querySelectorAll(Y),V)}catch(Z){}}return T(Y,X,V,W)};F.find=T.find;F.filter=T.filter;F.selectors=T.selectors;F.matches=T.matches})()}if(document.getElementsByClassName&&document.documentElement.getElementsByClassName){(function(){var T=document.createElement("div");T.innerHTML="<div class='test e'></div><div class='test'></div>";if(T.getElementsByClassName("e").length===0){return}T.lastChild.className="e";if(T.getElementsByClassName("e").length===1){return}I.order.splice(1,0,"CLASS");I.find.CLASS=function(U,V,W){if(typeof V.getElementsByClassName!=="undefined"&&!W){return V.getElementsByClassName(U[1])}}})()}function P(U,Z,Y,ad,aa,ac){var ab=U=="previousSibling"&&!ac;for(var W=0,V=ad.length;W<V;W++){var T=ad[W];if(T){if(ab&&T.nodeType===1){T.sizcache=Y;T.sizset=W}T=T[U];var X=false;while(T){if(T.sizcache===Y){X=ad[T.sizset];break}if(T.nodeType===1&&!ac){T.sizcache=Y;T.sizset=W}if(T.nodeName===Z){X=T;break}T=T[U]}ad[W]=X}}}function S(U,Z,Y,ad,aa,ac){var ab=U=="previousSibling"&&!ac;for(var W=0,V=ad.length;W<V;W++){var T=ad[W];if(T){if(ab&&T.nodeType===1){T.sizcache=Y;T.sizset=W}T=T[U];var X=false;while(T){if(T.sizcache===Y){X=ad[T.sizset];break}if(T.nodeType===1){if(!ac){T.sizcache=Y;T.sizset=W}if(typeof Z!=="string"){if(T===Z){X=true;break}}else{if(F.filter(Z,[T]).length>0){X=T;break}}}T=T[U]}ad[W]=X}}}var K=document.compareDocumentPosition?function(U,T){return U.compareDocumentPosition(T)&16}:function(U,T){return U!==T&&(U.contains?U.contains(T):true)};var Q=function(T){return T.nodeType===9&&T.documentElement.nodeName!=="HTML"||!!T.ownerDocument&&Q(T.ownerDocument)};var J=function(T,aa){var W=[],X="",Y,V=aa.nodeType?[aa]:aa;while((Y=I.match.PSEUDO.exec(T))){X+=Y[0];T=T.replace(I.match.PSEUDO,"")}T=I.relative[T]?T+"*":T;for(var Z=0,U=V.length;Z<U;Z++){F(T,V[Z],W)}return F.filter(X,W)};o.find=F;o.filter=F.filter;o.expr=F.selectors;o.expr[":"]=o.expr.filters;F.selectors.filters.hidden=function(T){return T.offsetWidth===0||T.offsetHeight===0};F.selectors.filters.visible=function(T){return T.offsetWidth>0||T.offsetHeight>0};F.selectors.filters.animated=function(T){return o.grep(o.timers,function(U){return T===U.elem}).length};o.multiFilter=function(V,T,U){if(U){V=":not("+V+")"}return F.matches(V,T)};o.dir=function(V,U){var T=[],W=V[U];while(W&&W!=document){if(W.nodeType==1){T.push(W)}W=W[U]}return T};o.nth=function(X,T,V,W){T=T||1;var U=0;for(;X;X=X[V]){if(X.nodeType==1&&++U==T){break}}return X};o.sibling=function(V,U){var T=[];for(;V;V=V.nextSibling){if(V.nodeType==1&&V!=U){T.push(V)}}return T};return;l.Sizzle=F})();o.event={add:function(I,F,H,K){if(I.nodeType==3||I.nodeType==8){return}if(I.setInterval&&I!=l){I=l}if(!H.guid){H.guid=this.guid++}if(K!==g){var G=H;H=this.proxy(G);H.data=K}var E=o.data(I,"events")||o.data(I,"events",{}),J=o.data(I,"handle")||o.data(I,"handle",function(){return typeof o!=="undefined"&&!o.event.triggered?o.event.handle.apply(arguments.callee.elem,arguments):g});J.elem=I;o.each(F.split(/\s+/),function(M,N){var O=N.split(".");N=O.shift();H.type=O.slice().sort().join(".");var L=E[N];if(o.event.specialAll[N]){o.event.specialAll[N].setup.call(I,K,O)}if(!L){L=E[N]={};if(!o.event.special[N]||o.event.special[N].setup.call(I,K,O)===false){if(I.addEventListener){I.addEventListener(N,J,false)}else{if(I.attachEvent){I.attachEvent("on"+N,J)}}}}L[H.guid]=H;o.event.global[N]=true});I=null},guid:1,global:{},remove:function(K,H,J){if(K.nodeType==3||K.nodeType==8){return}var G=o.data(K,"events"),F,E;if(G){if(H===g||(typeof H==="string"&&H.charAt(0)==".")){for(var I in G){this.remove(K,I+(H||""))}}else{if(H.type){J=H.handler;H=H.type}o.each(H.split(/\s+/),function(M,O){var Q=O.split(".");O=Q.shift();var N=RegExp("(^|\\.)"+Q.slice().sort().join(".*\\.")+"(\\.|$)");if(G[O]){if(J){delete G[O][J.guid]}else{for(var P in G[O]){if(N.test(G[O][P].type)){delete G[O][P]}}}if(o.event.specialAll[O]){o.event.specialAll[O].teardown.call(K,Q)}for(F in G[O]){break}if(!F){if(!o.event.special[O]||o.event.special[O].teardown.call(K,Q)===false){if(K.removeEventListener){K.removeEventListener(O,o.data(K,"handle"),false)}else{if(K.detachEvent){K.detachEvent("on"+O,o.data(K,"handle"))}}}F=null;delete G[O]}}})}for(F in G){break}if(!F){var L=o.data(K,"handle");if(L){L.elem=null}o.removeData(K,"events");o.removeData(K,"handle")}}},trigger:function(I,K,H,E){var G=I.type||I;if(!E){I=typeof I==="object"?I[h]?I:o.extend(o.Event(G),I):o.Event(G);if(G.indexOf("!")>=0){I.type=G=G.slice(0,-1);I.exclusive=true}if(!H){I.stopPropagation();if(this.global[G]){o.each(o.cache,function(){if(this.events&&this.events[G]){o.event.trigger(I,K,this.handle.elem)}})}}if(!H||H.nodeType==3||H.nodeType==8){return g}I.result=g;I.target=H;K=o.makeArray(K);K.unshift(I)}I.currentTarget=H;var J=o.data(H,"handle");if(J){J.apply(H,K)}if((!H[G]||(o.nodeName(H,"a")&&G=="click"))&&H["on"+G]&&H["on"+G].apply(H,K)===false){I.result=false}if(!E&&H[G]&&!I.isDefaultPrevented()&&!(o.nodeName(H,"a")&&G=="click")){this.triggered=true;try{H[G]()}catch(L){}}this.triggered=false;if(!I.isPropagationStopped()){var F=H.parentNode||H.ownerDocument;if(F){o.event.trigger(I,K,F,true)}}},handle:function(K){var J,E;K=arguments[0]=o.event.fix(K||l.event);K.currentTarget=this;var L=K.type.split(".");K.type=L.shift();J=!L.length&&!K.exclusive;var I=RegExp("(^|\\.)"+L.slice().sort().join(".*\\.")+"(\\.|$)");E=(o.data(this,"events")||{})[K.type];for(var G in E){var H=E[G];if(J||I.test(H.type)){K.handler=H;K.data=H.data;var F=H.apply(this,arguments);if(F!==g){K.result=F;if(F===false){K.preventDefault();K.stopPropagation()}}if(K.isImmediatePropagationStopped()){break}}}},props:"altKey attrChange attrName bubbles button cancelable charCode clientX clientY ctrlKey currentTarget data detail eventPhase fromElement handler keyCode metaKey newValue originalTarget pageX pageY prevValue relatedNode relatedTarget screenX screenY shiftKey srcElement target toElement view wheelDelta which".split(" "),fix:function(H){if(H[h]){return H}var F=H;H=o.Event(F);for(var G=this.props.length,J;G;){J=this.props[--G];H[J]=F[J]}if(!H.target){H.target=H.srcElement||document}if(H.target.nodeType==3){H.target=H.target.parentNode}if(!H.relatedTarget&&H.fromElement){H.relatedTarget=H.fromElement==H.target?H.toElement:H.fromElement}if(H.pageX==null&&H.clientX!=null){var I=document.documentElement,E=document.body;H.pageX=H.clientX+(I&&I.scrollLeft||E&&E.scrollLeft||0)-(I.clientLeft||0);H.pageY=H.clientY+(I&&I.scrollTop||E&&E.scrollTop||0)-(I.clientTop||0)}if(!H.which&&((H.charCode||H.charCode===0)?H.charCode:H.keyCode)){H.which=H.charCode||H.keyCode}if(!H.metaKey&&H.ctrlKey){H.metaKey=H.ctrlKey}if(!H.which&&H.button){H.which=(H.button&1?1:(H.button&2?3:(H.button&4?2:0)))}return H},proxy:function(F,E){E=E||function(){return F.apply(this,arguments)};E.guid=F.guid=F.guid||E.guid||this.guid++;return E},special:{ready:{setup:B,teardown:function(){}}},specialAll:{live:{setup:function(E,F){o.event.add(this,F[0],c)},teardown:function(G){if(G.length){var E=0,F=RegExp("(^|\\.)"+G[0]+"(\\.|$)");o.each((o.data(this,"events").live||{}),function(){if(F.test(this.type)){E++}});if(E<1){o.event.remove(this,G[0],c)}}}}}};o.Event=function(E){if(!this.preventDefault){return new o.Event(E)}if(E&&E.type){this.originalEvent=E;this.type=E.type}else{this.type=E}this.timeStamp=e();this[h]=true};function k(){return false}function u(){return true}o.Event.prototype={preventDefault:function(){this.isDefaultPrevented=u;var E=this.originalEvent;if(!E){return}if(E.preventDefault){E.preventDefault()}E.returnValue=false},stopPropagation:function(){this.isPropagationStopped=u;var E=this.originalEvent;if(!E){return}if(E.stopPropagation){E.stopPropagation()}E.cancelBubble=true},stopImmediatePropagation:function(){this.isImmediatePropagationStopped=u;this.stopPropagation()},isDefaultPrevented:k,isPropagationStopped:k,isImmediatePropagationStopped:k};var a=function(F){var E=F.relatedTarget;while(E&&E!=this){try{E=E.parentNode}catch(G){E=this}}if(E!=this){F.type=F.data;o.event.handle.apply(this,arguments)}};o.each({mouseover:"mouseenter",mouseout:"mouseleave"},function(F,E){o.event.special[E]={setup:function(){o.event.add(this,F,a,E)},teardown:function(){o.event.remove(this,F,a)}}});o.fn.extend({bind:function(F,G,E){return F=="unload"?this.one(F,G,E):this.each(function(){o.event.add(this,F,E||G,E&&G)})},one:function(G,H,F){var E=o.event.proxy(F||H,function(I){o(this).unbind(I,E);return(F||H).apply(this,arguments)});return this.each(function(){o.event.add(this,G,E,F&&H)})},unbind:function(F,E){return this.each(function(){o.event.remove(this,F,E)})},trigger:function(E,F){return this.each(function(){o.event.trigger(E,F,this)})},triggerHandler:function(E,G){if(this[0]){var F=o.Event(E);F.preventDefault();F.stopPropagation();o.event.trigger(F,G,this[0]);return F.result}},toggle:function(G){var E=arguments,F=1;while(F<E.length){o.event.proxy(G,E[F++])}return this.click(o.event.proxy(G,function(H){this.lastToggle=(this.lastToggle||0)%F;H.preventDefault();return E[this.lastToggle++].apply(this,arguments)||false}))},hover:function(E,F){return this.mouseenter(E).mouseleave(F)},ready:function(E){B();if(o.isReady){E.call(document,o)}else{o.readyList.push(E)}return this},live:function(G,F){var E=o.event.proxy(F);E.guid+=this.selector+G;o(document).bind(i(G,this.selector),this.selector,E);return this},die:function(F,E){o(document).unbind(i(F,this.selector),E?{guid:E.guid+this.selector+F}:null);return this}});function c(H){var E=RegExp("(^|\\.)"+H.type+"(\\.|$)"),G=true,F=[];o.each(o.data(this,"events").live||[],function(I,J){if(E.test(J.type)){var K=o(H.target).closest(J.data)[0];if(K){F.push({elem:K,fn:J})}}});F.sort(function(J,I){return o.data(J.elem,"closest")-o.data(I.elem,"closest")});o.each(F,function(){if(this.fn.call(this.elem,H,this.fn.data)===false){return(G=false)}});return G}function i(F,E){return["live",F,E.replace(/\./g,"`").replace(/ /g,"|")].join(".")}o.extend({isReady:false,readyList:[],ready:function(){if(!o.isReady){o.isReady=true;if(o.readyList){o.each(o.readyList,function(){this.call(document,o)});o.readyList=null}o(document).triggerHandler("ready")}}});var x=false;function B(){if(x){return}x=true;if(document.addEventListener){document.addEventListener("DOMContentLoaded",function(){document.removeEventListener("DOMContentLoaded",arguments.callee,false);o.ready()},false)}else{if(document.attachEvent){document.attachEvent("onreadystatechange",function(){if(document.readyState==="complete"){document.detachEvent("onreadystatechange",arguments.callee);o.ready()}});if(document.documentElement.doScroll&&l==l.top){(function(){if(o.isReady){return}try{document.documentElement.doScroll("left")}catch(E){setTimeout(arguments.callee,0);return}o.ready()})()}}}o.event.add(l,"load",o.ready)}o.each(("blur,focus,load,resize,scroll,unload,click,dblclick,mousedown,mouseup,mousemove,mouseover,mouseout,mouseenter,mouseleave,change,select,submit,keydown,keypress,keyup,error").split(","),function(F,E){o.fn[E]=function(G){return G?this.bind(E,G):this.trigger(E)}});o(l).bind("unload",function(){for(var E in o.cache){if(E!=1&&o.cache[E].handle){o.event.remove(o.cache[E].handle.elem)}}});(function(){o.support={};var F=document.documentElement,G=document.createElement("script"),K=document.createElement("div"),J="script"+(new Date).getTime();K.style.display="none";K.innerHTML='   <link/><table></table><a href="/a" style="color:red;float:left;opacity:.5;">a</a><select><option>text</option></select><object><param/></object>';var H=K.getElementsByTagName("*"),E=K.getElementsByTagName("a")[0];if(!H||!H.length||!E){return}o.support={leadingWhitespace:K.firstChild.nodeType==3,tbody:!K.getElementsByTagName("tbody").length,objectAll:!!K.getElementsByTagName("object")[0].getElementsByTagName("*").length,htmlSerialize:!!K.getElementsByTagName("link").length,style:/red/.test(E.getAttribute("style")),hrefNormalized:E.getAttribute("href")==="/a",opacity:E.style.opacity==="0.5",cssFloat:!!E.style.cssFloat,scriptEval:false,noCloneEvent:true,boxModel:null};G.type="text/javascript";try{G.appendChild(document.createTextNode("window."+J+"=1;"))}catch(I){}F.insertBefore(G,F.firstChild);if(l[J]){o.support.scriptEval=true;delete l[J]}F.removeChild(G);if(K.attachEvent&&K.fireEvent){K.attachEvent("onclick",function(){o.support.noCloneEvent=false;K.detachEvent("onclick",arguments.callee)});K.cloneNode(true).fireEvent("onclick")}o(function(){var L=document.createElement("div");L.style.width=L.style.paddingLeft="1px";document.body.appendChild(L);o.boxModel=o.support.boxModel=L.offsetWidth===2;document.body.removeChild(L).style.display="none"})})();var w=o.support.cssFloat?"cssFloat":"styleFloat";o.props={"for":"htmlFor","class":"className","float":w,cssFloat:w,styleFloat:w,readonly:"readOnly",maxlength:"maxLength",cellspacing:"cellSpacing",rowspan:"rowSpan",tabindex:"tabIndex"};o.fn.extend({_load:o.fn.load,load:function(G,J,K){if(typeof G!=="string"){return this._load(G)}var I=G.indexOf(" ");if(I>=0){var E=G.slice(I,G.length);G=G.slice(0,I)}var H="GET";if(J){if(o.isFunction(J)){K=J;J=null}else{if(typeof J==="object"){J=o.param(J);H="POST"}}}var F=this;o.ajax({url:G,type:H,dataType:"html",data:J,complete:function(M,L){if(L=="success"||L=="notmodified"){F.html(E?o("<div/>").append(M.responseText.replace(/<script(.|\s)*?\/script>/g,"")).find(E):M.responseText)}if(K){F.each(K,[M.responseText,L,M])}}});return this},serialize:function(){return o.param(this.serializeArray())},serializeArray:function(){return this.map(function(){return this.elements?o.makeArray(this.elements):this}).filter(function(){return this.name&&!this.disabled&&(this.checked||/select|textarea/i.test(this.nodeName)||/text|hidden|password|search/i.test(this.type))}).map(function(E,F){var G=o(this).val();return G==null?null:o.isArray(G)?o.map(G,function(I,H){return{name:F.name,value:I}}):{name:F.name,value:G}}).get()}});o.each("ajaxStart,ajaxStop,ajaxComplete,ajaxError,ajaxSuccess,ajaxSend".split(","),function(E,F){o.fn[F]=function(G){return this.bind(F,G)}});var r=e();o.extend({get:function(E,G,H,F){if(o.isFunction(G)){H=G;G=null}return o.ajax({type:"GET",url:E,data:G,success:H,dataType:F})},getScript:function(E,F){return o.get(E,null,F,"script")},getJSON:function(E,F,G){return o.get(E,F,G,"json")},post:function(E,G,H,F){if(o.isFunction(G)){H=G;G={}}return o.ajax({type:"POST",url:E,data:G,success:H,dataType:F})},ajaxSetup:function(E){o.extend(o.ajaxSettings,E)},ajaxSettings:{url:location.href,global:true,type:"GET",contentType:"application/x-www-form-urlencoded",processData:true,async:true,xhr:function(){return l.ActiveXObject?new ActiveXObject("Microsoft.XMLHTTP"):new XMLHttpRequest()},accepts:{xml:"application/xml, text/xml",html:"text/html",script:"text/javascript, application/javascript",json:"application/json, text/javascript",text:"text/plain",_default:"*/*"}},lastModified:{},ajax:function(M){M=o.extend(true,M,o.extend(true,{},o.ajaxSettings,M));var W,F=/=\?(&|$)/g,R,V,G=M.type.toUpperCase();if(M.data&&M.processData&&typeof M.data!=="string"){M.data=o.param(M.data)}if(M.dataType=="jsonp"){if(G=="GET"){if(!M.url.match(F)){M.url+=(M.url.match(/\?/)?"&":"?")+(M.jsonp||"callback")+"=?"}}else{if(!M.data||!M.data.match(F)){M.data=(M.data?M.data+"&":"")+(M.jsonp||"callback")+"=?"}}M.dataType="json"}if(M.dataType=="json"&&(M.data&&M.data.match(F)||M.url.match(F))){W="jsonp"+r++;if(M.data){M.data=(M.data+"").replace(F,"="+W+"$1")}M.url=M.url.replace(F,"="+W+"$1");M.dataType="script";l[W]=function(X){V=X;I();L();l[W]=g;try{delete l[W]}catch(Y){}if(H){H.removeChild(T)}}}if(M.dataType=="script"&&M.cache==null){M.cache=false}if(M.cache===false&&G=="GET"){var E=e();var U=M.url.replace(/(\?|&)_=.*?(&|$)/,"$1_="+E+"$2");M.url=U+((U==M.url)?(M.url.match(/\?/)?"&":"?")+"_="+E:"")}if(M.data&&G=="GET"){M.url+=(M.url.match(/\?/)?"&":"?")+M.data;M.data=null}if(M.global&&!o.active++){o.event.trigger("ajaxStart")}var Q=/^(\w+:)?\/\/([^\/?#]+)/.exec(M.url);if(M.dataType=="script"&&G=="GET"&&Q&&(Q[1]&&Q[1]!=location.protocol||Q[2]!=location.host)){var H=document.getElementsByTagName("head")[0];var T=document.createElement("script");T.src=M.url;if(M.scriptCharset){T.charset=M.scriptCharset}if(!W){var O=false;T.onload=T.onreadystatechange=function(){if(!O&&(!this.readyState||this.readyState=="loaded"||this.readyState=="complete")){O=true;I();L();T.onload=T.onreadystatechange=null;H.removeChild(T)}}}H.appendChild(T);return g}var K=false;var J=M.xhr();if(M.username){J.open(G,M.url,M.async,M.username,M.password)}else{J.open(G,M.url,M.async)}try{if(M.data){J.setRequestHeader("Content-Type",M.contentType)}if(M.ifModified){J.setRequestHeader("If-Modified-Since",o.lastModified[M.url]||"Thu, 01 Jan 1970 00:00:00 GMT")}J.setRequestHeader("X-Requested-With","XMLHttpRequest");J.setRequestHeader("Accept",M.dataType&&M.accepts[M.dataType]?M.accepts[M.dataType]+", */*":M.accepts._default)}catch(S){}if(M.beforeSend&&M.beforeSend(J,M)===false){if(M.global&&!--o.active){o.event.trigger("ajaxStop")}J.abort();return false}if(M.global){o.event.trigger("ajaxSend",[J,M])}var N=function(X){if(J.readyState==0){if(P){clearInterval(P);P=null;if(M.global&&!--o.active){o.event.trigger("ajaxStop")}}}else{if(!K&&J&&(J.readyState==4||X=="timeout")){K=true;if(P){clearInterval(P);P=null}R=X=="timeout"?"timeout":!o.httpSuccess(J)?"error":M.ifModified&&o.httpNotModified(J,M.url)?"notmodified":"success";if(R=="success"){try{V=o.httpData(J,M.dataType,M)}catch(Z){R="parsererror"}}if(R=="success"){var Y;try{Y=J.getResponseHeader("Last-Modified")}catch(Z){}if(M.ifModified&&Y){o.lastModified[M.url]=Y}if(!W){I()}}else{o.handleError(M,J,R)}L();if(X){J.abort()}if(M.async){J=null}}}};if(M.async){var P=setInterval(N,13);if(M.timeout>0){setTimeout(function(){if(J&&!K){N("timeout")}},M.timeout)}}try{J.send(M.data)}catch(S){o.handleError(M,J,null,S)}if(!M.async){N()}function I(){if(M.success){M.success(V,R)}if(M.global){o.event.trigger("ajaxSuccess",[J,M])}}function L(){if(M.complete){M.complete(J,R)}if(M.global){o.event.trigger("ajaxComplete",[J,M])}if(M.global&&!--o.active){o.event.trigger("ajaxStop")}}return J},handleError:function(F,H,E,G){if(F.error){F.error(H,E,G)}if(F.global){o.event.trigger("ajaxError",[H,F,G])}},active:0,httpSuccess:function(F){try{return !F.status&&location.protocol=="file:"||(F.status>=200&&F.status<300)||F.status==304||F.status==1223}catch(E){}return false},httpNotModified:function(G,E){try{var H=G.getResponseHeader("Last-Modified");return G.status==304||H==o.lastModified[E]}catch(F){}return false},httpData:function(J,H,G){var F=J.getResponseHeader("content-type"),E=H=="xml"||!H&&F&&F.indexOf("xml")>=0,I=E?J.responseXML:J.responseText;if(E&&I.documentElement.tagName=="parsererror"){throw"parsererror"}if(G&&G.dataFilter){I=G.dataFilter(I,H)}if(typeof I==="string"){if(H=="script"){o.globalEval(I)}if(H=="json"){I=l["eval"]("("+I+")")}}return I},param:function(E){var G=[];function H(I,J){G[G.length]=encodeURIComponent(I)+"="+encodeURIComponent(J)}if(o.isArray(E)||E.jquery){o.each(E,function(){H(this.name,this.value)})}else{for(var F in E){if(o.isArray(E[F])){o.each(E[F],function(){H(F,this)})}else{H(F,o.isFunction(E[F])?E[F]():E[F])}}}return G.join("&").replace(/%20/g,"+")}});var m={},n,d=[["height","marginTop","marginBottom","paddingTop","paddingBottom"],["width","marginLeft","marginRight","paddingLeft","paddingRight"],["opacity"]];function t(F,E){var G={};o.each(d.concat.apply([],d.slice(0,E)),function(){G[this]=F});return G}o.fn.extend({show:function(J,L){if(J){return this.animate(t("show",3),J,L)}else{for(var H=0,F=this.length;H<F;H++){var E=o.data(this[H],"olddisplay");this[H].style.display=E||"";if(o.css(this[H],"display")==="none"){var G=this[H].tagName,K;if(m[G]){K=m[G]}else{var I=o("<"+G+" />").appendTo("body");K=I.css("display");if(K==="none"){K="block"}I.remove();m[G]=K}o.data(this[H],"olddisplay",K)}}for(var H=0,F=this.length;H<F;H++){this[H].style.display=o.data(this[H],"olddisplay")||""}return this}},hide:function(H,I){if(H){return this.animate(t("hide",3),H,I)}else{for(var G=0,F=this.length;G<F;G++){var E=o.data(this[G],"olddisplay");if(!E&&E!=="none"){o.data(this[G],"olddisplay",o.css(this[G],"display"))}}for(var G=0,F=this.length;G<F;G++){this[G].style.display="none"}return this}},_toggle:o.fn.toggle,toggle:function(G,F){var E=typeof G==="boolean";return o.isFunction(G)&&o.isFunction(F)?this._toggle.apply(this,arguments):G==null||E?this.each(function(){var H=E?G:o(this).is(":hidden");o(this)[H?"show":"hide"]()}):this.animate(t("toggle",3),G,F)},fadeTo:function(E,G,F){return this.animate({opacity:G},E,F)},animate:function(I,F,H,G){var E=o.speed(F,H,G);return this[E.queue===false?"each":"queue"](function(){var K=o.extend({},E),M,L=this.nodeType==1&&o(this).is(":hidden"),J=this;for(M in I){if(I[M]=="hide"&&L||I[M]=="show"&&!L){return K.complete.call(this)}if((M=="height"||M=="width")&&this.style){K.display=o.css(this,"display");K.overflow=this.style.overflow}}if(K.overflow!=null){this.style.overflow="hidden"}K.curAnim=o.extend({},I);o.each(I,function(O,S){var R=new o.fx(J,K,O);if(/toggle|show|hide/.test(S)){R[S=="toggle"?L?"show":"hide":S](I)}else{var Q=S.toString().match(/^([+-]=)?([\d+-.]+)(.*)$/),T=R.cur(true)||0;if(Q){var N=parseFloat(Q[2]),P=Q[3]||"px";if(P!="px"){J.style[O]=(N||1)+P;T=((N||1)/R.cur(true))*T;J.style[O]=T+P}if(Q[1]){N=((Q[1]=="-="?-1:1)*N)+T}R.custom(T,N,P)}else{R.custom(T,S,"")}}});return true})},stop:function(F,E){var G=o.timers;if(F){this.queue([])}this.each(function(){for(var H=G.length-1;H>=0;H--){if(G[H].elem==this){if(E){G[H](true)}G.splice(H,1)}}});if(!E){this.dequeue()}return this}});o.each({slideDown:t("show",1),slideUp:t("hide",1),slideToggle:t("toggle",1),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"}},function(E,F){o.fn[E]=function(G,H){return this.animate(F,G,H)}});o.extend({speed:function(G,H,F){var E=typeof G==="object"?G:{complete:F||!F&&H||o.isFunction(G)&&G,duration:G,easing:F&&H||H&&!o.isFunction(H)&&H};E.duration=o.fx.off?0:typeof E.duration==="number"?E.duration:o.fx.speeds[E.duration]||o.fx.speeds._default;E.old=E.complete;E.complete=function(){if(E.queue!==false){o(this).dequeue()}if(o.isFunction(E.old)){E.old.call(this)}};return E},easing:{linear:function(G,H,E,F){return E+F*G},swing:function(G,H,E,F){return((-Math.cos(G*Math.PI)/2)+0.5)*F+E}},timers:[],fx:function(F,E,G){this.options=E;this.elem=F;this.prop=G;if(!E.orig){E.orig={}}}});o.fx.prototype={update:function(){if(this.options.step){this.options.step.call(this.elem,this.now,this)}(o.fx.step[this.prop]||o.fx.step._default)(this);if((this.prop=="height"||this.prop=="width")&&this.elem.style){this.elem.style.display="block"}},cur:function(F){if(this.elem[this.prop]!=null&&(!this.elem.style||this.elem.style[this.prop]==null)){return this.elem[this.prop]}var E=parseFloat(o.css(this.elem,this.prop,F));return E&&E>-10000?E:parseFloat(o.curCSS(this.elem,this.prop))||0},custom:function(I,H,G){this.startTime=e();this.start=I;this.end=H;this.unit=G||this.unit||"px";this.now=this.start;this.pos=this.state=0;var E=this;function F(J){return E.step(J)}F.elem=this.elem;if(F()&&o.timers.push(F)&&!n){n=setInterval(function(){var K=o.timers;for(var J=0;J<K.length;J++){if(!K[J]()){K.splice(J--,1)}}if(!K.length){clearInterval(n);n=g}},13)}},show:function(){this.options.orig[this.prop]=o.attr(this.elem.style,this.prop);this.options.show=true;this.custom(this.prop=="width"||this.prop=="height"?1:0,this.cur());o(this.elem).show()},hide:function(){this.options.orig[this.prop]=o.attr(this.elem.style,this.prop);this.options.hide=true;this.custom(this.cur(),0)},step:function(H){var G=e();if(H||G>=this.options.duration+this.startTime){this.now=this.end;this.pos=this.state=1;this.update();this.options.curAnim[this.prop]=true;var E=true;for(var F in this.options.curAnim){if(this.options.curAnim[F]!==true){E=false}}if(E){if(this.options.display!=null){this.elem.style.overflow=this.options.overflow;this.elem.style.display=this.options.display;if(o.css(this.elem,"display")=="none"){this.elem.style.display="block"}}if(this.options.hide){o(this.elem).hide()}if(this.options.hide||this.options.show){for(var I in this.options.curAnim){o.attr(this.elem.style,I,this.options.orig[I])}}this.options.complete.call(this.elem)}return false}else{var J=G-this.startTime;this.state=J/this.options.duration;this.pos=o.easing[this.options.easing||(o.easing.swing?"swing":"linear")](this.state,J,0,1,this.options.duration);this.now=this.start+((this.end-this.start)*this.pos);this.update()}return true}};o.extend(o.fx,{speeds:{slow:600,fast:200,_default:400},step:{opacity:function(E){o.attr(E.elem.style,"opacity",E.now)},_default:function(E){if(E.elem.style&&E.elem.style[E.prop]!=null){E.elem.style[E.prop]=E.now+E.unit}else{E.elem[E.prop]=E.now}}}});if(document.documentElement.getBoundingClientRect){o.fn.offset=function(){if(!this[0]){return{top:0,left:0}}if(this[0]===this[0].ownerDocument.body){return o.offset.bodyOffset(this[0])}var G=this[0].getBoundingClientRect(),J=this[0].ownerDocument,F=J.body,E=J.documentElement,L=E.clientTop||F.clientTop||0,K=E.clientLeft||F.clientLeft||0,I=G.top+(self.pageYOffset||o.boxModel&&E.scrollTop||F.scrollTop)-L,H=G.left+(self.pageXOffset||o.boxModel&&E.scrollLeft||F.scrollLeft)-K;return{top:I,left:H}}}else{o.fn.offset=function(){if(!this[0]){return{top:0,left:0}}if(this[0]===this[0].ownerDocument.body){return o.offset.bodyOffset(this[0])}o.offset.initialized||o.offset.initialize();var J=this[0],G=J.offsetParent,F=J,O=J.ownerDocument,M,H=O.documentElement,K=O.body,L=O.defaultView,E=L.getComputedStyle(J,null),N=J.offsetTop,I=J.offsetLeft;while((J=J.parentNode)&&J!==K&&J!==H){M=L.getComputedStyle(J,null);N-=J.scrollTop,I-=J.scrollLeft;if(J===G){N+=J.offsetTop,I+=J.offsetLeft;if(o.offset.doesNotAddBorder&&!(o.offset.doesAddBorderForTableAndCells&&/^t(able|d|h)$/i.test(J.tagName))){N+=parseInt(M.borderTopWidth,10)||0,I+=parseInt(M.borderLeftWidth,10)||0}F=G,G=J.offsetParent}if(o.offset.subtractsBorderForOverflowNotVisible&&M.overflow!=="visible"){N+=parseInt(M.borderTopWidth,10)||0,I+=parseInt(M.borderLeftWidth,10)||0}E=M}if(E.position==="relative"||E.position==="static"){N+=K.offsetTop,I+=K.offsetLeft}if(E.position==="fixed"){N+=Math.max(H.scrollTop,K.scrollTop),I+=Math.max(H.scrollLeft,K.scrollLeft)}return{top:N,left:I}}}o.offset={initialize:function(){if(this.initialized){return}var L=document.body,F=document.createElement("div"),H,G,N,I,M,E,J=L.style.marginTop,K='<div style="position:absolute;top:0;left:0;margin:0;border:5px solid #000;padding:0;width:1px;height:1px;"><div></div></div><table style="position:absolute;top:0;left:0;margin:0;border:5px solid #000;padding:0;width:1px;height:1px;" cellpadding="0" cellspacing="0"><tr><td></td></tr></table>';M={position:"absolute",top:0,left:0,margin:0,border:0,width:"1px",height:"1px",visibility:"hidden"};for(E in M){F.style[E]=M[E]}F.innerHTML=K;L.insertBefore(F,L.firstChild);H=F.firstChild,G=H.firstChild,I=H.nextSibling.firstChild.firstChild;this.doesNotAddBorder=(G.offsetTop!==5);this.doesAddBorderForTableAndCells=(I.offsetTop===5);H.style.overflow="hidden",H.style.position="relative";this.subtractsBorderForOverflowNotVisible=(G.offsetTop===-5);L.style.marginTop="1px";this.doesNotIncludeMarginInBodyOffset=(L.offsetTop===0);L.style.marginTop=J;L.removeChild(F);this.initialized=true},bodyOffset:function(E){o.offset.initialized||o.offset.initialize();var G=E.offsetTop,F=E.offsetLeft;if(o.offset.doesNotIncludeMarginInBodyOffset){G+=parseInt(o.curCSS(E,"marginTop",true),10)||0,F+=parseInt(o.curCSS(E,"marginLeft",true),10)||0}return{top:G,left:F}}};o.fn.extend({position:function(){var I=0,H=0,F;if(this[0]){var G=this.offsetParent(),J=this.offset(),E=/^body|html$/i.test(G[0].tagName)?{top:0,left:0}:G.offset();J.top-=j(this,"marginTop");J.left-=j(this,"marginLeft");E.top+=j(G,"borderTopWidth");E.left+=j(G,"borderLeftWidth");F={top:J.top-E.top,left:J.left-E.left}}return F},offsetParent:function(){var E=this[0].offsetParent||document.body;while(E&&(!/^body|html$/i.test(E.tagName)&&o.css(E,"position")=="static")){E=E.offsetParent}return o(E)}});o.each(["Left","Top"],function(F,E){var G="scroll"+E;o.fn[G]=function(H){if(!this[0]){return null}return H!==g?this.each(function(){this==l||this==document?l.scrollTo(!F?H:o(l).scrollLeft(),F?H:o(l).scrollTop()):this[G]=H}):this[0]==l||this[0]==document?self[F?"pageYOffset":"pageXOffset"]||o.boxModel&&document.documentElement[G]||document.body[G]:this[0][G]}});o.each(["Height","Width"],function(I,G){var E=I?"Left":"Top",H=I?"Right":"Bottom",F=G.toLowerCase();o.fn["inner"+G]=function(){return this[0]?o.css(this[0],F,false,"padding"):null};o.fn["outer"+G]=function(K){return this[0]?o.css(this[0],F,false,K?"margin":"border"):null};var J=G.toLowerCase();o.fn[J]=function(K){return this[0]==l?document.compatMode=="CSS1Compat"&&document.documentElement["client"+G]||document.body["client"+G]:this[0]==document?Math.max(document.documentElement["client"+G],document.body["scroll"+G],document.documentElement["scroll"+G],document.body["offset"+G],document.documentElement["offset"+G]):K===g?(this.length?o.css(this[0],J):null):this.css(J,typeof K==="string"?K:K+"px")}})})();;/*
 * jQuery UI 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI
 */
jQuery.ui||(function(c){var i=c.fn.remove,d=c.browser.mozilla&&(parseFloat(c.browser.version)<1.9);c.ui={version:"1.7.2",plugin:{add:function(k,l,n){var m=c.ui[k].prototype;for(var j in n){m.plugins[j]=m.plugins[j]||[];m.plugins[j].push([l,n[j]])}},call:function(j,l,k){var n=j.plugins[l];if(!n||!j.element[0].parentNode){return}for(var m=0;m<n.length;m++){if(j.options[n[m][0]]){n[m][1].apply(j.element,k)}}}},contains:function(k,j){return document.compareDocumentPosition?k.compareDocumentPosition(j)&16:k!==j&&k.contains(j)},hasScroll:function(m,k){if(c(m).css("overflow")=="hidden"){return false}var j=(k&&k=="left")?"scrollLeft":"scrollTop",l=false;if(m[j]>0){return true}m[j]=1;l=(m[j]>0);m[j]=0;return l},isOverAxis:function(k,j,l){return(k>j)&&(k<(j+l))},isOver:function(o,k,n,m,j,l){return c.ui.isOverAxis(o,n,j)&&c.ui.isOverAxis(k,m,l)},keyCode:{BACKSPACE:8,CAPS_LOCK:20,COMMA:188,CONTROL:17,DELETE:46,DOWN:40,END:35,ENTER:13,ESCAPE:27,HOME:36,INSERT:45,LEFT:37,NUMPAD_ADD:107,NUMPAD_DECIMAL:110,NUMPAD_DIVIDE:111,NUMPAD_ENTER:108,NUMPAD_MULTIPLY:106,NUMPAD_SUBTRACT:109,PAGE_DOWN:34,PAGE_UP:33,PERIOD:190,RIGHT:39,SHIFT:16,SPACE:32,TAB:9,UP:38}};if(d){var f=c.attr,e=c.fn.removeAttr,h="http://www.w3.org/2005/07/aaa",a=/^aria-/,b=/^wairole:/;c.attr=function(k,j,l){var m=l!==undefined;return(j=="role"?(m?f.call(this,k,j,"wairole:"+l):(f.apply(this,arguments)||"").replace(b,"")):(a.test(j)?(m?k.setAttributeNS(h,j.replace(a,"aaa:"),l):f.call(this,k,j.replace(a,"aaa:"))):f.apply(this,arguments)))};c.fn.removeAttr=function(j){return(a.test(j)?this.each(function(){this.removeAttributeNS(h,j.replace(a,""))}):e.call(this,j))}}c.fn.extend({remove:function(){c("*",this).add(this).each(function(){c(this).triggerHandler("remove")});return i.apply(this,arguments)},enableSelection:function(){return this.attr("unselectable","off").css("MozUserSelect","").unbind("selectstart.ui")},disableSelection:function(){return this.attr("unselectable","on").css("MozUserSelect","none").bind("selectstart.ui",function(){return false})},scrollParent:function(){var j;if((c.browser.msie&&(/(static|relative)/).test(this.css("position")))||(/absolute/).test(this.css("position"))){j=this.parents().filter(function(){return(/(relative|absolute|fixed)/).test(c.curCSS(this,"position",1))&&(/(auto|scroll)/).test(c.curCSS(this,"overflow",1)+c.curCSS(this,"overflow-y",1)+c.curCSS(this,"overflow-x",1))}).eq(0)}else{j=this.parents().filter(function(){return(/(auto|scroll)/).test(c.curCSS(this,"overflow",1)+c.curCSS(this,"overflow-y",1)+c.curCSS(this,"overflow-x",1))}).eq(0)}return(/fixed/).test(this.css("position"))||!j.length?c(document):j}});c.extend(c.expr[":"],{data:function(l,k,j){return !!c.data(l,j[3])},focusable:function(k){var l=k.nodeName.toLowerCase(),j=c.attr(k,"tabindex");return(/input|select|textarea|button|object/.test(l)?!k.disabled:"a"==l||"area"==l?k.href||!isNaN(j):!isNaN(j))&&!c(k)["area"==l?"parents":"closest"](":hidden").length},tabbable:function(k){var j=c.attr(k,"tabindex");return(isNaN(j)||j>=0)&&c(k).is(":focusable")}});function g(m,n,o,l){function k(q){var p=c[m][n][q]||[];return(typeof p=="string"?p.split(/,?\s+/):p)}var j=k("getter");if(l.length==1&&typeof l[0]=="string"){j=j.concat(k("getterSetter"))}return(c.inArray(o,j)!=-1)}c.widget=function(k,j){var l=k.split(".")[0];k=k.split(".")[1];c.fn[k]=function(p){var n=(typeof p=="string"),o=Array.prototype.slice.call(arguments,1);if(n&&p.substring(0,1)=="_"){return this}if(n&&g(l,k,p,o)){var m=c.data(this[0],k);return(m?m[p].apply(m,o):undefined)}return this.each(function(){var q=c.data(this,k);(!q&&!n&&c.data(this,k,new c[l][k](this,p))._init());(q&&n&&c.isFunction(q[p])&&q[p].apply(q,o))})};c[l]=c[l]||{};c[l][k]=function(o,n){var m=this;this.namespace=l;this.widgetName=k;this.widgetEventPrefix=c[l][k].eventPrefix||k;this.widgetBaseClass=l+"-"+k;this.options=c.extend({},c.widget.defaults,c[l][k].defaults,c.metadata&&c.metadata.get(o)[k],n);this.element=c(o).bind("setData."+k,function(q,p,r){if(q.target==o){return m._setData(p,r)}}).bind("getData."+k,function(q,p){if(q.target==o){return m._getData(p)}}).bind("remove",function(){return m.destroy()})};c[l][k].prototype=c.extend({},c.widget.prototype,j);c[l][k].getterSetter="option"};c.widget.prototype={_init:function(){},destroy:function(){this.element.removeData(this.widgetName).removeClass(this.widgetBaseClass+"-disabled "+this.namespace+"-state-disabled").removeAttr("aria-disabled")},option:function(l,m){var k=l,j=this;if(typeof l=="string"){if(m===undefined){return this._getData(l)}k={};k[l]=m}c.each(k,function(n,o){j._setData(n,o)})},_getData:function(j){return this.options[j]},_setData:function(j,k){this.options[j]=k;if(j=="disabled"){this.element[k?"addClass":"removeClass"](this.widgetBaseClass+"-disabled "+this.namespace+"-state-disabled").attr("aria-disabled",k)}},enable:function(){this._setData("disabled",false)},disable:function(){this._setData("disabled",true)},_trigger:function(l,m,n){var p=this.options[l],j=(l==this.widgetEventPrefix?l:this.widgetEventPrefix+l);m=c.Event(m);m.type=j;if(m.originalEvent){for(var k=c.event.props.length,o;k;){o=c.event.props[--k];m[o]=m.originalEvent[o]}}this.element.trigger(m,n);return !(c.isFunction(p)&&p.call(this.element[0],m,n)===false||m.isDefaultPrevented())}};c.widget.defaults={disabled:false};c.ui.mouse={_mouseInit:function(){var j=this;this.element.bind("mousedown."+this.widgetName,function(k){return j._mouseDown(k)}).bind("click."+this.widgetName,function(k){if(j._preventClickEvent){j._preventClickEvent=false;k.stopImmediatePropagation();return false}});if(c.browser.msie){this._mouseUnselectable=this.element.attr("unselectable");this.element.attr("unselectable","on")}this.started=false},_mouseDestroy:function(){this.element.unbind("."+this.widgetName);(c.browser.msie&&this.element.attr("unselectable",this._mouseUnselectable))},_mouseDown:function(l){l.originalEvent=l.originalEvent||{};if(l.originalEvent.mouseHandled){return}(this._mouseStarted&&this._mouseUp(l));this._mouseDownEvent=l;var k=this,m=(l.which==1),j=(typeof this.options.cancel=="string"?c(l.target).parents().add(l.target).filter(this.options.cancel).length:false);if(!m||j||!this._mouseCapture(l)){return true}this.mouseDelayMet=!this.options.delay;if(!this.mouseDelayMet){this._mouseDelayTimer=setTimeout(function(){k.mouseDelayMet=true},this.options.delay)}if(this._mouseDistanceMet(l)&&this._mouseDelayMet(l)){this._mouseStarted=(this._mouseStart(l)!==false);if(!this._mouseStarted){l.preventDefault();return true}}this._mouseMoveDelegate=function(n){return k._mouseMove(n)};this._mouseUpDelegate=function(n){return k._mouseUp(n)};c(document).bind("mousemove."+this.widgetName,this._mouseMoveDelegate).bind("mouseup."+this.widgetName,this._mouseUpDelegate);(c.browser.safari||l.preventDefault());l.originalEvent.mouseHandled=true;return true},_mouseMove:function(j){if(c.browser.msie&&!j.button){return this._mouseUp(j)}if(this._mouseStarted){this._mouseDrag(j);return j.preventDefault()}if(this._mouseDistanceMet(j)&&this._mouseDelayMet(j)){this._mouseStarted=(this._mouseStart(this._mouseDownEvent,j)!==false);(this._mouseStarted?this._mouseDrag(j):this._mouseUp(j))}return !this._mouseStarted},_mouseUp:function(j){c(document).unbind("mousemove."+this.widgetName,this._mouseMoveDelegate).unbind("mouseup."+this.widgetName,this._mouseUpDelegate);if(this._mouseStarted){this._mouseStarted=false;this._preventClickEvent=(j.target==this._mouseDownEvent.target);this._mouseStop(j)}return false},_mouseDistanceMet:function(j){return(Math.max(Math.abs(this._mouseDownEvent.pageX-j.pageX),Math.abs(this._mouseDownEvent.pageY-j.pageY))>=this.options.distance)},_mouseDelayMet:function(j){return this.mouseDelayMet},_mouseStart:function(j){},_mouseDrag:function(j){},_mouseStop:function(j){},_mouseCapture:function(j){return true}};c.ui.mouse.defaults={cancel:null,distance:1,delay:0}})(jQuery);;/*
 * jQuery UI Draggable 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Draggables
 *
 * Depends:
 *	ui.core.js
 */
(function(a){a.widget("ui.draggable",a.extend({},a.ui.mouse,{_init:function(){if(this.options.helper=="original"&&!(/^(?:r|a|f)/).test(this.element.css("position"))){this.element[0].style.position="relative"}(this.options.addClasses&&this.element.addClass("ui-draggable"));(this.options.disabled&&this.element.addClass("ui-draggable-disabled"));this._mouseInit()},destroy:function(){if(!this.element.data("draggable")){return}this.element.removeData("draggable").unbind(".draggable").removeClass("ui-draggable ui-draggable-dragging ui-draggable-disabled");this._mouseDestroy()},_mouseCapture:function(b){var c=this.options;if(this.helper||c.disabled||a(b.target).is(".ui-resizable-handle")){return false}this.handle=this._getHandle(b);if(!this.handle){return false}return true},_mouseStart:function(b){var c=this.options;this.helper=this._createHelper(b);this._cacheHelperProportions();if(a.ui.ddmanager){a.ui.ddmanager.current=this}this._cacheMargins();this.cssPosition=this.helper.css("position");this.scrollParent=this.helper.scrollParent();this.offset=this.element.offset();this.offset={top:this.offset.top-this.margins.top,left:this.offset.left-this.margins.left};a.extend(this.offset,{click:{left:b.pageX-this.offset.left,top:b.pageY-this.offset.top},parent:this._getParentOffset(),relative:this._getRelativeOffset()});this.originalPosition=this._generatePosition(b);this.originalPageX=b.pageX;this.originalPageY=b.pageY;if(c.cursorAt){this._adjustOffsetFromHelper(c.cursorAt)}if(c.containment){this._setContainment()}this._trigger("start",b);this._cacheHelperProportions();if(a.ui.ddmanager&&!c.dropBehaviour){a.ui.ddmanager.prepareOffsets(this,b)}this.helper.addClass("ui-draggable-dragging");this._mouseDrag(b,true);return true},_mouseDrag:function(b,d){this.position=this._generatePosition(b);this.positionAbs=this._convertPositionTo("absolute");if(!d){var c=this._uiHash();this._trigger("drag",b,c);this.position=c.position}if(!this.options.axis||this.options.axis!="y"){this.helper[0].style.left=this.position.left+"px"}if(!this.options.axis||this.options.axis!="x"){this.helper[0].style.top=this.position.top+"px"}if(a.ui.ddmanager){a.ui.ddmanager.drag(this,b)}return false},_mouseStop:function(c){var d=false;if(a.ui.ddmanager&&!this.options.dropBehaviour){d=a.ui.ddmanager.drop(this,c)}if(this.dropped){d=this.dropped;this.dropped=false}if((this.options.revert=="invalid"&&!d)||(this.options.revert=="valid"&&d)||this.options.revert===true||(a.isFunction(this.options.revert)&&this.options.revert.call(this.element,d))){var b=this;a(this.helper).animate(this.originalPosition,parseInt(this.options.revertDuration,10),function(){b._trigger("stop",c);b._clear()})}else{this._trigger("stop",c);this._clear()}return false},_getHandle:function(b){var c=!this.options.handle||!a(this.options.handle,this.element).length?true:false;a(this.options.handle,this.element).find("*").andSelf().each(function(){if(this==b.target){c=true}});return c},_createHelper:function(c){var d=this.options;var b=a.isFunction(d.helper)?a(d.helper.apply(this.element[0],[c])):(d.helper=="clone"?this.element.clone():this.element);if(!b.parents("body").length){b.appendTo((d.appendTo=="parent"?this.element[0].parentNode:d.appendTo))}if(b[0]!=this.element[0]&&!(/(fixed|absolute)/).test(b.css("position"))){b.css("position","absolute")}return b},_adjustOffsetFromHelper:function(b){if(b.left!=undefined){this.offset.click.left=b.left+this.margins.left}if(b.right!=undefined){this.offset.click.left=this.helperProportions.width-b.right+this.margins.left}if(b.top!=undefined){this.offset.click.top=b.top+this.margins.top}if(b.bottom!=undefined){this.offset.click.top=this.helperProportions.height-b.bottom+this.margins.top}},_getParentOffset:function(){this.offsetParent=this.helper.offsetParent();var b=this.offsetParent.offset();if(this.cssPosition=="absolute"&&this.scrollParent[0]!=document&&a.ui.contains(this.scrollParent[0],this.offsetParent[0])){b.left+=this.scrollParent.scrollLeft();b.top+=this.scrollParent.scrollTop()}if((this.offsetParent[0]==document.body)||(this.offsetParent[0].tagName&&this.offsetParent[0].tagName.toLowerCase()=="html"&&a.browser.msie)){b={top:0,left:0}}return{top:b.top+(parseInt(this.offsetParent.css("borderTopWidth"),10)||0),left:b.left+(parseInt(this.offsetParent.css("borderLeftWidth"),10)||0)}},_getRelativeOffset:function(){if(this.cssPosition=="relative"){var b=this.element.position();return{top:b.top-(parseInt(this.helper.css("top"),10)||0)+this.scrollParent.scrollTop(),left:b.left-(parseInt(this.helper.css("left"),10)||0)+this.scrollParent.scrollLeft()}}else{return{top:0,left:0}}},_cacheMargins:function(){this.margins={left:(parseInt(this.element.css("marginLeft"),10)||0),top:(parseInt(this.element.css("marginTop"),10)||0)}},_cacheHelperProportions:function(){this.helperProportions={width:this.helper.outerWidth(),height:this.helper.outerHeight()}},_setContainment:function(){var e=this.options;if(e.containment=="parent"){e.containment=this.helper[0].parentNode}if(e.containment=="document"||e.containment=="window"){this.containment=[0-this.offset.relative.left-this.offset.parent.left,0-this.offset.relative.top-this.offset.parent.top,a(e.containment=="document"?document:window).width()-this.helperProportions.width-this.margins.left,(a(e.containment=="document"?document:window).height()||document.body.parentNode.scrollHeight)-this.helperProportions.height-this.margins.top]}if(!(/^(document|window|parent)$/).test(e.containment)&&e.containment.constructor!=Array){var c=a(e.containment)[0];if(!c){return}var d=a(e.containment).offset();var b=(a(c).css("overflow")!="hidden");this.containment=[d.left+(parseInt(a(c).css("borderLeftWidth"),10)||0)+(parseInt(a(c).css("paddingLeft"),10)||0)-this.margins.left,d.top+(parseInt(a(c).css("borderTopWidth"),10)||0)+(parseInt(a(c).css("paddingTop"),10)||0)-this.margins.top,d.left+(b?Math.max(c.scrollWidth,c.offsetWidth):c.offsetWidth)-(parseInt(a(c).css("borderLeftWidth"),10)||0)-(parseInt(a(c).css("paddingRight"),10)||0)-this.helperProportions.width-this.margins.left,d.top+(b?Math.max(c.scrollHeight,c.offsetHeight):c.offsetHeight)-(parseInt(a(c).css("borderTopWidth"),10)||0)-(parseInt(a(c).css("paddingBottom"),10)||0)-this.helperProportions.height-this.margins.top]}else{if(e.containment.constructor==Array){this.containment=e.containment}}},_convertPositionTo:function(f,h){if(!h){h=this.position}var c=f=="absolute"?1:-1;var e=this.options,b=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&a.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,g=(/(html|body)/i).test(b[0].tagName);return{top:(h.top+this.offset.relative.top*c+this.offset.parent.top*c-(a.browser.safari&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollTop():(g?0:b.scrollTop()))*c)),left:(h.left+this.offset.relative.left*c+this.offset.parent.left*c-(a.browser.safari&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():g?0:b.scrollLeft())*c))}},_generatePosition:function(e){var h=this.options,b=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&a.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,i=(/(html|body)/i).test(b[0].tagName);if(this.cssPosition=="relative"&&!(this.scrollParent[0]!=document&&this.scrollParent[0]!=this.offsetParent[0])){this.offset.relative=this._getRelativeOffset()}var d=e.pageX;var c=e.pageY;if(this.originalPosition){if(this.containment){if(e.pageX-this.offset.click.left<this.containment[0]){d=this.containment[0]+this.offset.click.left}if(e.pageY-this.offset.click.top<this.containment[1]){c=this.containment[1]+this.offset.click.top}if(e.pageX-this.offset.click.left>this.containment[2]){d=this.containment[2]+this.offset.click.left}if(e.pageY-this.offset.click.top>this.containment[3]){c=this.containment[3]+this.offset.click.top}}if(h.grid){var g=this.originalPageY+Math.round((c-this.originalPageY)/h.grid[1])*h.grid[1];c=this.containment?(!(g-this.offset.click.top<this.containment[1]||g-this.offset.click.top>this.containment[3])?g:(!(g-this.offset.click.top<this.containment[1])?g-h.grid[1]:g+h.grid[1])):g;var f=this.originalPageX+Math.round((d-this.originalPageX)/h.grid[0])*h.grid[0];d=this.containment?(!(f-this.offset.click.left<this.containment[0]||f-this.offset.click.left>this.containment[2])?f:(!(f-this.offset.click.left<this.containment[0])?f-h.grid[0]:f+h.grid[0])):f}}return{top:(c-this.offset.click.top-this.offset.relative.top-this.offset.parent.top+(a.browser.safari&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollTop():(i?0:b.scrollTop())))),left:(d-this.offset.click.left-this.offset.relative.left-this.offset.parent.left+(a.browser.safari&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():i?0:b.scrollLeft())))}},_clear:function(){this.helper.removeClass("ui-draggable-dragging");if(this.helper[0]!=this.element[0]&&!this.cancelHelperRemoval){this.helper.remove()}this.helper=null;this.cancelHelperRemoval=false},_trigger:function(b,c,d){d=d||this._uiHash();a.ui.plugin.call(this,b,[c,d]);if(b=="drag"){this.positionAbs=this._convertPositionTo("absolute")}return a.widget.prototype._trigger.call(this,b,c,d)},plugins:{},_uiHash:function(b){return{helper:this.helper,position:this.position,absolutePosition:this.positionAbs,offset:this.positionAbs}}}));a.extend(a.ui.draggable,{version:"1.7.2",eventPrefix:"drag",defaults:{addClasses:true,appendTo:"parent",axis:false,cancel:":input,option",connectToSortable:false,containment:false,cursor:"auto",cursorAt:false,delay:0,distance:1,grid:false,handle:false,helper:"original",iframeFix:false,opacity:false,refreshPositions:false,revert:false,revertDuration:500,scope:"default",scroll:true,scrollSensitivity:20,scrollSpeed:20,snap:false,snapMode:"both",snapTolerance:20,stack:false,zIndex:false}});a.ui.plugin.add("draggable","connectToSortable",{start:function(c,e){var d=a(this).data("draggable"),f=d.options,b=a.extend({},e,{item:d.element});d.sortables=[];a(f.connectToSortable).each(function(){var g=a.data(this,"sortable");if(g&&!g.options.disabled){d.sortables.push({instance:g,shouldRevert:g.options.revert});g._refreshItems();g._trigger("activate",c,b)}})},stop:function(c,e){var d=a(this).data("draggable"),b=a.extend({},e,{item:d.element});a.each(d.sortables,function(){if(this.instance.isOver){this.instance.isOver=0;d.cancelHelperRemoval=true;this.instance.cancelHelperRemoval=false;if(this.shouldRevert){this.instance.options.revert=true}this.instance._mouseStop(c);this.instance.options.helper=this.instance.options._helper;if(d.options.helper=="original"){this.instance.currentItem.css({top:"auto",left:"auto"})}}else{this.instance.cancelHelperRemoval=false;this.instance._trigger("deactivate",c,b)}})},drag:function(c,f){var e=a(this).data("draggable"),b=this;var d=function(i){var n=this.offset.click.top,m=this.offset.click.left;var g=this.positionAbs.top,k=this.positionAbs.left;var j=i.height,l=i.width;var p=i.top,h=i.left;return a.ui.isOver(g+n,k+m,p,h,j,l)};a.each(e.sortables,function(g){this.instance.positionAbs=e.positionAbs;this.instance.helperProportions=e.helperProportions;this.instance.offset.click=e.offset.click;if(this.instance._intersectsWith(this.instance.containerCache)){if(!this.instance.isOver){this.instance.isOver=1;this.instance.currentItem=a(b).clone().appendTo(this.instance.element).data("sortable-item",true);this.instance.options._helper=this.instance.options.helper;this.instance.options.helper=function(){return f.helper[0]};c.target=this.instance.currentItem[0];this.instance._mouseCapture(c,true);this.instance._mouseStart(c,true,true);this.instance.offset.click.top=e.offset.click.top;this.instance.offset.click.left=e.offset.click.left;this.instance.offset.parent.left-=e.offset.parent.left-this.instance.offset.parent.left;this.instance.offset.parent.top-=e.offset.parent.top-this.instance.offset.parent.top;e._trigger("toSortable",c);e.dropped=this.instance.element;e.currentItem=e.element;this.instance.fromOutside=e}if(this.instance.currentItem){this.instance._mouseDrag(c)}}else{if(this.instance.isOver){this.instance.isOver=0;this.instance.cancelHelperRemoval=true;this.instance.options.revert=false;this.instance._trigger("out",c,this.instance._uiHash(this.instance));this.instance._mouseStop(c,true);this.instance.options.helper=this.instance.options._helper;this.instance.currentItem.remove();if(this.instance.placeholder){this.instance.placeholder.remove()}e._trigger("fromSortable",c);e.dropped=false}}})}});a.ui.plugin.add("draggable","cursor",{start:function(c,d){var b=a("body"),e=a(this).data("draggable").options;if(b.css("cursor")){e._cursor=b.css("cursor")}b.css("cursor",e.cursor)},stop:function(b,c){var d=a(this).data("draggable").options;if(d._cursor){a("body").css("cursor",d._cursor)}}});a.ui.plugin.add("draggable","iframeFix",{start:function(b,c){var d=a(this).data("draggable").options;a(d.iframeFix===true?"iframe":d.iframeFix).each(function(){a('<div class="ui-draggable-iframeFix" style="background: #fff;"></div>').css({width:this.offsetWidth+"px",height:this.offsetHeight+"px",position:"absolute",opacity:"0.001",zIndex:1000}).css(a(this).offset()).appendTo("body")})},stop:function(b,c){a("div.ui-draggable-iframeFix").each(function(){this.parentNode.removeChild(this)})}});a.ui.plugin.add("draggable","opacity",{start:function(c,d){var b=a(d.helper),e=a(this).data("draggable").options;if(b.css("opacity")){e._opacity=b.css("opacity")}b.css("opacity",e.opacity)},stop:function(b,c){var d=a(this).data("draggable").options;if(d._opacity){a(c.helper).css("opacity",d._opacity)}}});a.ui.plugin.add("draggable","scroll",{start:function(c,d){var b=a(this).data("draggable");if(b.scrollParent[0]!=document&&b.scrollParent[0].tagName!="HTML"){b.overflowOffset=b.scrollParent.offset()}},drag:function(d,e){var c=a(this).data("draggable"),f=c.options,b=false;if(c.scrollParent[0]!=document&&c.scrollParent[0].tagName!="HTML"){if(!f.axis||f.axis!="x"){if((c.overflowOffset.top+c.scrollParent[0].offsetHeight)-d.pageY<f.scrollSensitivity){c.scrollParent[0].scrollTop=b=c.scrollParent[0].scrollTop+f.scrollSpeed}else{if(d.pageY-c.overflowOffset.top<f.scrollSensitivity){c.scrollParent[0].scrollTop=b=c.scrollParent[0].scrollTop-f.scrollSpeed}}}if(!f.axis||f.axis!="y"){if((c.overflowOffset.left+c.scrollParent[0].offsetWidth)-d.pageX<f.scrollSensitivity){c.scrollParent[0].scrollLeft=b=c.scrollParent[0].scrollLeft+f.scrollSpeed}else{if(d.pageX-c.overflowOffset.left<f.scrollSensitivity){c.scrollParent[0].scrollLeft=b=c.scrollParent[0].scrollLeft-f.scrollSpeed}}}}else{if(!f.axis||f.axis!="x"){if(d.pageY-a(document).scrollTop()<f.scrollSensitivity){b=a(document).scrollTop(a(document).scrollTop()-f.scrollSpeed)}else{if(a(window).height()-(d.pageY-a(document).scrollTop())<f.scrollSensitivity){b=a(document).scrollTop(a(document).scrollTop()+f.scrollSpeed)}}}if(!f.axis||f.axis!="y"){if(d.pageX-a(document).scrollLeft()<f.scrollSensitivity){b=a(document).scrollLeft(a(document).scrollLeft()-f.scrollSpeed)}else{if(a(window).width()-(d.pageX-a(document).scrollLeft())<f.scrollSensitivity){b=a(document).scrollLeft(a(document).scrollLeft()+f.scrollSpeed)}}}}if(b!==false&&a.ui.ddmanager&&!f.dropBehaviour){a.ui.ddmanager.prepareOffsets(c,d)}}});a.ui.plugin.add("draggable","snap",{start:function(c,d){var b=a(this).data("draggable"),e=b.options;b.snapElements=[];a(e.snap.constructor!=String?(e.snap.items||":data(draggable)"):e.snap).each(function(){var g=a(this);var f=g.offset();if(this!=b.element[0]){b.snapElements.push({item:this,width:g.outerWidth(),height:g.outerHeight(),top:f.top,left:f.left})}})},drag:function(u,p){var g=a(this).data("draggable"),q=g.options;var y=q.snapTolerance;var x=p.offset.left,w=x+g.helperProportions.width,f=p.offset.top,e=f+g.helperProportions.height;for(var v=g.snapElements.length-1;v>=0;v--){var s=g.snapElements[v].left,n=s+g.snapElements[v].width,m=g.snapElements[v].top,A=m+g.snapElements[v].height;if(!((s-y<x&&x<n+y&&m-y<f&&f<A+y)||(s-y<x&&x<n+y&&m-y<e&&e<A+y)||(s-y<w&&w<n+y&&m-y<f&&f<A+y)||(s-y<w&&w<n+y&&m-y<e&&e<A+y))){if(g.snapElements[v].snapping){(g.options.snap.release&&g.options.snap.release.call(g.element,u,a.extend(g._uiHash(),{snapItem:g.snapElements[v].item})))}g.snapElements[v].snapping=false;continue}if(q.snapMode!="inner"){var c=Math.abs(m-e)<=y;var z=Math.abs(A-f)<=y;var j=Math.abs(s-w)<=y;var k=Math.abs(n-x)<=y;if(c){p.position.top=g._convertPositionTo("relative",{top:m-g.helperProportions.height,left:0}).top-g.margins.top}if(z){p.position.top=g._convertPositionTo("relative",{top:A,left:0}).top-g.margins.top}if(j){p.position.left=g._convertPositionTo("relative",{top:0,left:s-g.helperProportions.width}).left-g.margins.left}if(k){p.position.left=g._convertPositionTo("relative",{top:0,left:n}).left-g.margins.left}}var h=(c||z||j||k);if(q.snapMode!="outer"){var c=Math.abs(m-f)<=y;var z=Math.abs(A-e)<=y;var j=Math.abs(s-x)<=y;var k=Math.abs(n-w)<=y;if(c){p.position.top=g._convertPositionTo("relative",{top:m,left:0}).top-g.margins.top}if(z){p.position.top=g._convertPositionTo("relative",{top:A-g.helperProportions.height,left:0}).top-g.margins.top}if(j){p.position.left=g._convertPositionTo("relative",{top:0,left:s}).left-g.margins.left}if(k){p.position.left=g._convertPositionTo("relative",{top:0,left:n-g.helperProportions.width}).left-g.margins.left}}if(!g.snapElements[v].snapping&&(c||z||j||k||h)){(g.options.snap.snap&&g.options.snap.snap.call(g.element,u,a.extend(g._uiHash(),{snapItem:g.snapElements[v].item})))}g.snapElements[v].snapping=(c||z||j||k||h)}}});a.ui.plugin.add("draggable","stack",{start:function(b,c){var e=a(this).data("draggable").options;var d=a.makeArray(a(e.stack.group)).sort(function(g,f){return(parseInt(a(g).css("zIndex"),10)||e.stack.min)-(parseInt(a(f).css("zIndex"),10)||e.stack.min)});a(d).each(function(f){this.style.zIndex=e.stack.min+f});this[0].style.zIndex=e.stack.min+d.length}});a.ui.plugin.add("draggable","zIndex",{start:function(c,d){var b=a(d.helper),e=a(this).data("draggable").options;if(b.css("zIndex")){e._zIndex=b.css("zIndex")}b.css("zIndex",e.zIndex)},stop:function(b,c){var d=a(this).data("draggable").options;if(d._zIndex){a(c.helper).css("zIndex",d._zIndex)}}})})(jQuery);;/*
 * jQuery UI Droppable 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Droppables
 *
 * Depends:
 *	ui.core.js
 *	ui.draggable.js
 */
(function(a){a.widget("ui.droppable",{_init:function(){var c=this.options,b=c.accept;this.isover=0;this.isout=1;this.options.accept=this.options.accept&&a.isFunction(this.options.accept)?this.options.accept:function(e){return e.is(b)};this.proportions={width:this.element[0].offsetWidth,height:this.element[0].offsetHeight};a.ui.ddmanager.droppables[this.options.scope]=a.ui.ddmanager.droppables[this.options.scope]||[];a.ui.ddmanager.droppables[this.options.scope].push(this);(this.options.addClasses&&this.element.addClass("ui-droppable"))},destroy:function(){var b=a.ui.ddmanager.droppables[this.options.scope];for(var c=0;c<b.length;c++){if(b[c]==this){b.splice(c,1)}}this.element.removeClass("ui-droppable ui-droppable-disabled").removeData("droppable").unbind(".droppable")},_setData:function(b,c){if(b=="accept"){this.options.accept=c&&a.isFunction(c)?c:function(e){return e.is(c)}}else{a.widget.prototype._setData.apply(this,arguments)}},_activate:function(c){var b=a.ui.ddmanager.current;if(this.options.activeClass){this.element.addClass(this.options.activeClass)}(b&&this._trigger("activate",c,this.ui(b)))},_deactivate:function(c){var b=a.ui.ddmanager.current;if(this.options.activeClass){this.element.removeClass(this.options.activeClass)}(b&&this._trigger("deactivate",c,this.ui(b)))},_over:function(c){var b=a.ui.ddmanager.current;if(!b||(b.currentItem||b.element)[0]==this.element[0]){return}if(this.options.accept.call(this.element[0],(b.currentItem||b.element))){if(this.options.hoverClass){this.element.addClass(this.options.hoverClass)}this._trigger("over",c,this.ui(b))}},_out:function(c){var b=a.ui.ddmanager.current;if(!b||(b.currentItem||b.element)[0]==this.element[0]){return}if(this.options.accept.call(this.element[0],(b.currentItem||b.element))){if(this.options.hoverClass){this.element.removeClass(this.options.hoverClass)}this._trigger("out",c,this.ui(b))}},_drop:function(c,d){var b=d||a.ui.ddmanager.current;if(!b||(b.currentItem||b.element)[0]==this.element[0]){return false}var e=false;this.element.find(":data(droppable)").not(".ui-draggable-dragging").each(function(){var f=a.data(this,"droppable");if(f.options.greedy&&a.ui.intersect(b,a.extend(f,{offset:f.element.offset()}),f.options.tolerance)){e=true;return false}});if(e){return false}if(this.options.accept.call(this.element[0],(b.currentItem||b.element))){if(this.options.activeClass){this.element.removeClass(this.options.activeClass)}if(this.options.hoverClass){this.element.removeClass(this.options.hoverClass)}this._trigger("drop",c,this.ui(b));return this.element}return false},ui:function(b){return{draggable:(b.currentItem||b.element),helper:b.helper,position:b.position,absolutePosition:b.positionAbs,offset:b.positionAbs}}});a.extend(a.ui.droppable,{version:"1.7.2",eventPrefix:"drop",defaults:{accept:"*",activeClass:false,addClasses:true,greedy:false,hoverClass:false,scope:"default",tolerance:"intersect"}});a.ui.intersect=function(q,j,o){if(!j.offset){return false}var e=(q.positionAbs||q.position.absolute).left,d=e+q.helperProportions.width,n=(q.positionAbs||q.position.absolute).top,m=n+q.helperProportions.height;var g=j.offset.left,c=g+j.proportions.width,p=j.offset.top,k=p+j.proportions.height;switch(o){case"fit":return(g<e&&d<c&&p<n&&m<k);break;case"intersect":return(g<e+(q.helperProportions.width/2)&&d-(q.helperProportions.width/2)<c&&p<n+(q.helperProportions.height/2)&&m-(q.helperProportions.height/2)<k);break;case"pointer":var h=((q.positionAbs||q.position.absolute).left+(q.clickOffset||q.offset.click).left),i=((q.positionAbs||q.position.absolute).top+(q.clickOffset||q.offset.click).top),f=a.ui.isOver(i,h,p,g,j.proportions.height,j.proportions.width);return f;break;case"touch":return((n>=p&&n<=k)||(m>=p&&m<=k)||(n<p&&m>k))&&((e>=g&&e<=c)||(d>=g&&d<=c)||(e<g&&d>c));break;default:return false;break}};a.ui.ddmanager={current:null,droppables:{"default":[]},prepareOffsets:function(e,g){var b=a.ui.ddmanager.droppables[e.options.scope];var f=g?g.type:null;var h=(e.currentItem||e.element).find(":data(droppable)").andSelf();droppablesLoop:for(var d=0;d<b.length;d++){if(b[d].options.disabled||(e&&!b[d].options.accept.call(b[d].element[0],(e.currentItem||e.element)))){continue}for(var c=0;c<h.length;c++){if(h[c]==b[d].element[0]){b[d].proportions.height=0;continue droppablesLoop}}b[d].visible=b[d].element.css("display")!="none";if(!b[d].visible){continue}b[d].offset=b[d].element.offset();b[d].proportions={width:b[d].element[0].offsetWidth,height:b[d].element[0].offsetHeight};if(f=="mousedown"){b[d]._activate.call(b[d],g)}}},drop:function(b,c){var d=false;a.each(a.ui.ddmanager.droppables[b.options.scope],function(){if(!this.options){return}if(!this.options.disabled&&this.visible&&a.ui.intersect(b,this,this.options.tolerance)){d=this._drop.call(this,c)}if(!this.options.disabled&&this.visible&&this.options.accept.call(this.element[0],(b.currentItem||b.element))){this.isout=1;this.isover=0;this._deactivate.call(this,c)}});return d},drag:function(b,c){if(b.options.refreshPositions){a.ui.ddmanager.prepareOffsets(b,c)}a.each(a.ui.ddmanager.droppables[b.options.scope],function(){if(this.options.disabled||this.greedyChild||!this.visible){return}var e=a.ui.intersect(b,this,this.options.tolerance);var g=!e&&this.isover==1?"isout":(e&&this.isover==0?"isover":null);if(!g){return}var f;if(this.options.greedy){var d=this.element.parents(":data(droppable):eq(0)");if(d.length){f=a.data(d[0],"droppable");f.greedyChild=(g=="isover"?1:0)}}if(f&&g=="isover"){f.isover=0;f.isout=1;f._out.call(f,c)}this[g]=1;this[g=="isout"?"isover":"isout"]=0;this[g=="isover"?"_over":"_out"].call(this,c);if(f&&g=="isout"){f.isout=0;f.isover=1;f._over.call(f,c)}})}}})(jQuery);;/*
 * jQuery UI Resizable 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Resizables
 *
 * Depends:
 *	ui.core.js
 */
(function(c){c.widget("ui.resizable",c.extend({},c.ui.mouse,{_init:function(){var e=this,j=this.options;this.element.addClass("ui-resizable");c.extend(this,{_aspectRatio:!!(j.aspectRatio),aspectRatio:j.aspectRatio,originalElement:this.element,_proportionallyResizeElements:[],_helper:j.helper||j.ghost||j.animate?j.helper||"ui-resizable-helper":null});if(this.element[0].nodeName.match(/canvas|textarea|input|select|button|img/i)){if(/relative/.test(this.element.css("position"))&&c.browser.opera){this.element.css({position:"relative",top:"auto",left:"auto"})}this.element.wrap(c('<div class="ui-wrapper" style="overflow: hidden;"></div>').css({position:this.element.css("position"),width:this.element.outerWidth(),height:this.element.outerHeight(),top:this.element.css("top"),left:this.element.css("left")}));this.element=this.element.parent().data("resizable",this.element.data("resizable"));this.elementIsWrapper=true;this.element.css({marginLeft:this.originalElement.css("marginLeft"),marginTop:this.originalElement.css("marginTop"),marginRight:this.originalElement.css("marginRight"),marginBottom:this.originalElement.css("marginBottom")});this.originalElement.css({marginLeft:0,marginTop:0,marginRight:0,marginBottom:0});this.originalResizeStyle=this.originalElement.css("resize");this.originalElement.css("resize","none");this._proportionallyResizeElements.push(this.originalElement.css({position:"static",zoom:1,display:"block"}));this.originalElement.css({margin:this.originalElement.css("margin")});this._proportionallyResize()}this.handles=j.handles||(!c(".ui-resizable-handle",this.element).length?"e,s,se":{n:".ui-resizable-n",e:".ui-resizable-e",s:".ui-resizable-s",w:".ui-resizable-w",se:".ui-resizable-se",sw:".ui-resizable-sw",ne:".ui-resizable-ne",nw:".ui-resizable-nw"});if(this.handles.constructor==String){if(this.handles=="all"){this.handles="n,e,s,w,se,sw,ne,nw"}var k=this.handles.split(",");this.handles={};for(var f=0;f<k.length;f++){var h=c.trim(k[f]),d="ui-resizable-"+h;var g=c('<div class="ui-resizable-handle '+d+'"></div>');if(/sw|se|ne|nw/.test(h)){g.css({zIndex:++j.zIndex})}if("se"==h){g.addClass("ui-icon ui-icon-gripsmall-diagonal-se")}this.handles[h]=".ui-resizable-"+h;this.element.append(g)}}this._renderAxis=function(p){p=p||this.element;for(var m in this.handles){if(this.handles[m].constructor==String){this.handles[m]=c(this.handles[m],this.element).show()}if(this.elementIsWrapper&&this.originalElement[0].nodeName.match(/textarea|input|select|button/i)){var n=c(this.handles[m],this.element),o=0;o=/sw|ne|nw|se|n|s/.test(m)?n.outerHeight():n.outerWidth();var l=["padding",/ne|nw|n/.test(m)?"Top":/se|sw|s/.test(m)?"Bottom":/^e$/.test(m)?"Right":"Left"].join("");p.css(l,o);this._proportionallyResize()}if(!c(this.handles[m]).length){continue}}};this._renderAxis(this.element);this._handles=c(".ui-resizable-handle",this.element).disableSelection();this._handles.mouseover(function(){if(!e.resizing){if(this.className){var i=this.className.match(/ui-resizable-(se|sw|ne|nw|n|e|s|w)/i)}e.axis=i&&i[1]?i[1]:"se"}});if(j.autoHide){this._handles.hide();c(this.element).addClass("ui-resizable-autohide").hover(function(){c(this).removeClass("ui-resizable-autohide");e._handles.show()},function(){if(!e.resizing){c(this).addClass("ui-resizable-autohide");e._handles.hide()}})}this._mouseInit()},destroy:function(){this._mouseDestroy();var d=function(f){c(f).removeClass("ui-resizable ui-resizable-disabled ui-resizable-resizing").removeData("resizable").unbind(".resizable").find(".ui-resizable-handle").remove()};if(this.elementIsWrapper){d(this.element);var e=this.element;e.parent().append(this.originalElement.css({position:e.css("position"),width:e.outerWidth(),height:e.outerHeight(),top:e.css("top"),left:e.css("left")})).end().remove()}this.originalElement.css("resize",this.originalResizeStyle);d(this.originalElement)},_mouseCapture:function(e){var f=false;for(var d in this.handles){if(c(this.handles[d])[0]==e.target){f=true}}return this.options.disabled||!!f},_mouseStart:function(f){var i=this.options,e=this.element.position(),d=this.element;this.resizing=true;this.documentScroll={top:c(document).scrollTop(),left:c(document).scrollLeft()};if(d.is(".ui-draggable")||(/absolute/).test(d.css("position"))){d.css({position:"absolute",top:e.top,left:e.left})}if(c.browser.opera&&(/relative/).test(d.css("position"))){d.css({position:"relative",top:"auto",left:"auto"})}this._renderProxy();var j=b(this.helper.css("left")),g=b(this.helper.css("top"));if(i.containment){j+=c(i.containment).scrollLeft()||0;g+=c(i.containment).scrollTop()||0}this.offset=this.helper.offset();this.position={left:j,top:g};this.size=this._helper?{width:d.outerWidth(),height:d.outerHeight()}:{width:d.width(),height:d.height()};this.originalSize=this._helper?{width:d.outerWidth(),height:d.outerHeight()}:{width:d.width(),height:d.height()};this.originalPosition={left:j,top:g};this.sizeDiff={width:d.outerWidth()-d.width(),height:d.outerHeight()-d.height()};this.originalMousePosition={left:f.pageX,top:f.pageY};this.aspectRatio=(typeof i.aspectRatio=="number")?i.aspectRatio:((this.originalSize.width/this.originalSize.height)||1);var h=c(".ui-resizable-"+this.axis).css("cursor");c("body").css("cursor",h=="auto"?this.axis+"-resize":h);d.addClass("ui-resizable-resizing");this._propagate("start",f);return true},_mouseDrag:function(d){var g=this.helper,f=this.options,l={},p=this,i=this.originalMousePosition,m=this.axis;var q=(d.pageX-i.left)||0,n=(d.pageY-i.top)||0;var h=this._change[m];if(!h){return false}var k=h.apply(this,[d,q,n]),j=c.browser.msie&&c.browser.version<7,e=this.sizeDiff;if(this._aspectRatio||d.shiftKey){k=this._updateRatio(k,d)}k=this._respectSize(k,d);this._propagate("resize",d);g.css({top:this.position.top+"px",left:this.position.left+"px",width:this.size.width+"px",height:this.size.height+"px"});if(!this._helper&&this._proportionallyResizeElements.length){this._proportionallyResize()}this._updateCache(k);this._trigger("resize",d,this.ui());return false},_mouseStop:function(g){this.resizing=false;var h=this.options,l=this;if(this._helper){var f=this._proportionallyResizeElements,d=f.length&&(/textarea/i).test(f[0].nodeName),e=d&&c.ui.hasScroll(f[0],"left")?0:l.sizeDiff.height,j=d?0:l.sizeDiff.width;var m={width:(l.size.width-j),height:(l.size.height-e)},i=(parseInt(l.element.css("left"),10)+(l.position.left-l.originalPosition.left))||null,k=(parseInt(l.element.css("top"),10)+(l.position.top-l.originalPosition.top))||null;if(!h.animate){this.element.css(c.extend(m,{top:k,left:i}))}l.helper.height(l.size.height);l.helper.width(l.size.width);if(this._helper&&!h.animate){this._proportionallyResize()}}c("body").css("cursor","auto");this.element.removeClass("ui-resizable-resizing");this._propagate("stop",g);if(this._helper){this.helper.remove()}return false},_updateCache:function(d){var e=this.options;this.offset=this.helper.offset();if(a(d.left)){this.position.left=d.left}if(a(d.top)){this.position.top=d.top}if(a(d.height)){this.size.height=d.height}if(a(d.width)){this.size.width=d.width}},_updateRatio:function(g,f){var h=this.options,i=this.position,e=this.size,d=this.axis;if(g.height){g.width=(e.height*this.aspectRatio)}else{if(g.width){g.height=(e.width/this.aspectRatio)}}if(d=="sw"){g.left=i.left+(e.width-g.width);g.top=null}if(d=="nw"){g.top=i.top+(e.height-g.height);g.left=i.left+(e.width-g.width)}return g},_respectSize:function(k,f){var i=this.helper,h=this.options,q=this._aspectRatio||f.shiftKey,p=this.axis,s=a(k.width)&&h.maxWidth&&(h.maxWidth<k.width),l=a(k.height)&&h.maxHeight&&(h.maxHeight<k.height),g=a(k.width)&&h.minWidth&&(h.minWidth>k.width),r=a(k.height)&&h.minHeight&&(h.minHeight>k.height);if(g){k.width=h.minWidth}if(r){k.height=h.minHeight}if(s){k.width=h.maxWidth}if(l){k.height=h.maxHeight}var e=this.originalPosition.left+this.originalSize.width,n=this.position.top+this.size.height;var j=/sw|nw|w/.test(p),d=/nw|ne|n/.test(p);if(g&&j){k.left=e-h.minWidth}if(s&&j){k.left=e-h.maxWidth}if(r&&d){k.top=n-h.minHeight}if(l&&d){k.top=n-h.maxHeight}var m=!k.width&&!k.height;if(m&&!k.left&&k.top){k.top=null}else{if(m&&!k.top&&k.left){k.left=null}}return k},_proportionallyResize:function(){var j=this.options;if(!this._proportionallyResizeElements.length){return}var f=this.helper||this.element;for(var e=0;e<this._proportionallyResizeElements.length;e++){var g=this._proportionallyResizeElements[e];if(!this.borderDif){var d=[g.css("borderTopWidth"),g.css("borderRightWidth"),g.css("borderBottomWidth"),g.css("borderLeftWidth")],h=[g.css("paddingTop"),g.css("paddingRight"),g.css("paddingBottom"),g.css("paddingLeft")];this.borderDif=c.map(d,function(k,m){var l=parseInt(k,10)||0,n=parseInt(h[m],10)||0;return l+n})}if(c.browser.msie&&!(!(c(f).is(":hidden")||c(f).parents(":hidden").length))){continue}g.css({height:(f.height()-this.borderDif[0]-this.borderDif[2])||0,width:(f.width()-this.borderDif[1]-this.borderDif[3])||0})}},_renderProxy:function(){var e=this.element,h=this.options;this.elementOffset=e.offset();if(this._helper){this.helper=this.helper||c('<div style="overflow:hidden;"></div>');var d=c.browser.msie&&c.browser.version<7,f=(d?1:0),g=(d?2:-1);this.helper.addClass(this._helper).css({width:this.element.outerWidth()+g,height:this.element.outerHeight()+g,position:"absolute",left:this.elementOffset.left-f+"px",top:this.elementOffset.top-f+"px",zIndex:++h.zIndex});this.helper.appendTo("body").disableSelection()}else{this.helper=this.element}},_change:{e:function(f,e,d){return{width:this.originalSize.width+e}},w:function(g,e,d){var i=this.options,f=this.originalSize,h=this.originalPosition;return{left:h.left+e,width:f.width-e}},n:function(g,e,d){var i=this.options,f=this.originalSize,h=this.originalPosition;return{top:h.top+d,height:f.height-d}},s:function(f,e,d){return{height:this.originalSize.height+d}},se:function(f,e,d){return c.extend(this._change.s.apply(this,arguments),this._change.e.apply(this,[f,e,d]))},sw:function(f,e,d){return c.extend(this._change.s.apply(this,arguments),this._change.w.apply(this,[f,e,d]))},ne:function(f,e,d){return c.extend(this._change.n.apply(this,arguments),this._change.e.apply(this,[f,e,d]))},nw:function(f,e,d){return c.extend(this._change.n.apply(this,arguments),this._change.w.apply(this,[f,e,d]))}},_propagate:function(e,d){c.ui.plugin.call(this,e,[d,this.ui()]);(e!="resize"&&this._trigger(e,d,this.ui()))},plugins:{},ui:function(){return{originalElement:this.originalElement,element:this.element,helper:this.helper,position:this.position,size:this.size,originalSize:this.originalSize,originalPosition:this.originalPosition}}}));c.extend(c.ui.resizable,{version:"1.7.2",eventPrefix:"resize",defaults:{alsoResize:false,animate:false,animateDuration:"slow",animateEasing:"swing",aspectRatio:false,autoHide:false,cancel:":input,option",containment:false,delay:0,distance:1,ghost:false,grid:false,handles:"e,s,se",helper:false,maxHeight:null,maxWidth:null,minHeight:10,minWidth:10,zIndex:1000}});c.ui.plugin.add("resizable","alsoResize",{start:function(e,f){var d=c(this).data("resizable"),g=d.options;_store=function(h){c(h).each(function(){c(this).data("resizable-alsoresize",{width:parseInt(c(this).width(),10),height:parseInt(c(this).height(),10),left:parseInt(c(this).css("left"),10),top:parseInt(c(this).css("top"),10)})})};if(typeof(g.alsoResize)=="object"&&!g.alsoResize.parentNode){if(g.alsoResize.length){g.alsoResize=g.alsoResize[0];_store(g.alsoResize)}else{c.each(g.alsoResize,function(h,i){_store(h)})}}else{_store(g.alsoResize)}},resize:function(f,h){var e=c(this).data("resizable"),i=e.options,g=e.originalSize,k=e.originalPosition;var j={height:(e.size.height-g.height)||0,width:(e.size.width-g.width)||0,top:(e.position.top-k.top)||0,left:(e.position.left-k.left)||0},d=function(l,m){c(l).each(function(){var p=c(this),q=c(this).data("resizable-alsoresize"),o={},n=m&&m.length?m:["width","height","top","left"];c.each(n||["width","height","top","left"],function(r,t){var s=(q[t]||0)+(j[t]||0);if(s&&s>=0){o[t]=s||null}});if(/relative/.test(p.css("position"))&&c.browser.opera){e._revertToRelativePosition=true;p.css({position:"absolute",top:"auto",left:"auto"})}p.css(o)})};if(typeof(i.alsoResize)=="object"&&!i.alsoResize.nodeType){c.each(i.alsoResize,function(l,m){d(l,m)})}else{d(i.alsoResize)}},stop:function(e,f){var d=c(this).data("resizable");if(d._revertToRelativePosition&&c.browser.opera){d._revertToRelativePosition=false;el.css({position:"relative"})}c(this).removeData("resizable-alsoresize-start")}});c.ui.plugin.add("resizable","animate",{stop:function(h,m){var n=c(this).data("resizable"),i=n.options;var g=n._proportionallyResizeElements,d=g.length&&(/textarea/i).test(g[0].nodeName),e=d&&c.ui.hasScroll(g[0],"left")?0:n.sizeDiff.height,k=d?0:n.sizeDiff.width;var f={width:(n.size.width-k),height:(n.size.height-e)},j=(parseInt(n.element.css("left"),10)+(n.position.left-n.originalPosition.left))||null,l=(parseInt(n.element.css("top"),10)+(n.position.top-n.originalPosition.top))||null;n.element.animate(c.extend(f,l&&j?{top:l,left:j}:{}),{duration:i.animateDuration,easing:i.animateEasing,step:function(){var o={width:parseInt(n.element.css("width"),10),height:parseInt(n.element.css("height"),10),top:parseInt(n.element.css("top"),10),left:parseInt(n.element.css("left"),10)};if(g&&g.length){c(g[0]).css({width:o.width,height:o.height})}n._updateCache(o);n._propagate("resize",h)}})}});c.ui.plugin.add("resizable","containment",{start:function(e,q){var s=c(this).data("resizable"),i=s.options,k=s.element;var f=i.containment,j=(f instanceof c)?f.get(0):(/parent/.test(f))?k.parent().get(0):f;if(!j){return}s.containerElement=c(j);if(/document/.test(f)||f==document){s.containerOffset={left:0,top:0};s.containerPosition={left:0,top:0};s.parentData={element:c(document),left:0,top:0,width:c(document).width(),height:c(document).height()||document.body.parentNode.scrollHeight}}else{var m=c(j),h=[];c(["Top","Right","Left","Bottom"]).each(function(p,o){h[p]=b(m.css("padding"+o))});s.containerOffset=m.offset();s.containerPosition=m.position();s.containerSize={height:(m.innerHeight()-h[3]),width:(m.innerWidth()-h[1])};var n=s.containerOffset,d=s.containerSize.height,l=s.containerSize.width,g=(c.ui.hasScroll(j,"left")?j.scrollWidth:l),r=(c.ui.hasScroll(j)?j.scrollHeight:d);s.parentData={element:j,left:n.left,top:n.top,width:g,height:r}}},resize:function(f,p){var s=c(this).data("resizable"),h=s.options,e=s.containerSize,n=s.containerOffset,l=s.size,m=s.position,q=s._aspectRatio||f.shiftKey,d={top:0,left:0},g=s.containerElement;if(g[0]!=document&&(/static/).test(g.css("position"))){d=n}if(m.left<(s._helper?n.left:0)){s.size.width=s.size.width+(s._helper?(s.position.left-n.left):(s.position.left-d.left));if(q){s.size.height=s.size.width/h.aspectRatio}s.position.left=h.helper?n.left:0}if(m.top<(s._helper?n.top:0)){s.size.height=s.size.height+(s._helper?(s.position.top-n.top):s.position.top);if(q){s.size.width=s.size.height*h.aspectRatio}s.position.top=s._helper?n.top:0}s.offset.left=s.parentData.left+s.position.left;s.offset.top=s.parentData.top+s.position.top;var k=Math.abs((s._helper?s.offset.left-d.left:(s.offset.left-d.left))+s.sizeDiff.width),r=Math.abs((s._helper?s.offset.top-d.top:(s.offset.top-n.top))+s.sizeDiff.height);var j=s.containerElement.get(0)==s.element.parent().get(0),i=/relative|absolute/.test(s.containerElement.css("position"));if(j&&i){k-=s.parentData.left}if(k+s.size.width>=s.parentData.width){s.size.width=s.parentData.width-k;if(q){s.size.height=s.size.width/s.aspectRatio}}if(r+s.size.height>=s.parentData.height){s.size.height=s.parentData.height-r;if(q){s.size.width=s.size.height*s.aspectRatio}}},stop:function(e,m){var p=c(this).data("resizable"),f=p.options,k=p.position,l=p.containerOffset,d=p.containerPosition,g=p.containerElement;var i=c(p.helper),q=i.offset(),n=i.outerWidth()-p.sizeDiff.width,j=i.outerHeight()-p.sizeDiff.height;if(p._helper&&!f.animate&&(/relative/).test(g.css("position"))){c(this).css({left:q.left-d.left-l.left,width:n,height:j})}if(p._helper&&!f.animate&&(/static/).test(g.css("position"))){c(this).css({left:q.left-d.left-l.left,width:n,height:j})}}});c.ui.plugin.add("resizable","ghost",{start:function(f,g){var d=c(this).data("resizable"),h=d.options,e=d.size;d.ghost=d.originalElement.clone();d.ghost.css({opacity:0.25,display:"block",position:"relative",height:e.height,width:e.width,margin:0,left:0,top:0}).addClass("ui-resizable-ghost").addClass(typeof h.ghost=="string"?h.ghost:"");d.ghost.appendTo(d.helper)},resize:function(e,f){var d=c(this).data("resizable"),g=d.options;if(d.ghost){d.ghost.css({position:"relative",height:d.size.height,width:d.size.width})}},stop:function(e,f){var d=c(this).data("resizable"),g=d.options;if(d.ghost&&d.helper){d.helper.get(0).removeChild(d.ghost.get(0))}}});c.ui.plugin.add("resizable","grid",{resize:function(d,l){var n=c(this).data("resizable"),g=n.options,j=n.size,h=n.originalSize,i=n.originalPosition,m=n.axis,k=g._aspectRatio||d.shiftKey;g.grid=typeof g.grid=="number"?[g.grid,g.grid]:g.grid;var f=Math.round((j.width-h.width)/(g.grid[0]||1))*(g.grid[0]||1),e=Math.round((j.height-h.height)/(g.grid[1]||1))*(g.grid[1]||1);if(/^(se|s|e)$/.test(m)){n.size.width=h.width+f;n.size.height=h.height+e}else{if(/^(ne)$/.test(m)){n.size.width=h.width+f;n.size.height=h.height+e;n.position.top=i.top-e}else{if(/^(sw)$/.test(m)){n.size.width=h.width+f;n.size.height=h.height+e;n.position.left=i.left-f}else{n.size.width=h.width+f;n.size.height=h.height+e;n.position.top=i.top-e;n.position.left=i.left-f}}}}});var b=function(d){return parseInt(d,10)||0};var a=function(d){return !isNaN(parseInt(d,10))}})(jQuery);;/*
 * jQuery UI Selectable 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Selectables
 *
 * Depends:
 *	ui.core.js
 */
(function(a){a.widget("ui.selectable",a.extend({},a.ui.mouse,{_init:function(){var b=this;this.element.addClass("ui-selectable");this.dragged=false;var c;this.refresh=function(){c=a(b.options.filter,b.element[0]);c.each(function(){var d=a(this);var e=d.offset();a.data(this,"selectable-item",{element:this,$element:d,left:e.left,top:e.top,right:e.left+d.outerWidth(),bottom:e.top+d.outerHeight(),startselected:false,selected:d.hasClass("ui-selected"),selecting:d.hasClass("ui-selecting"),unselecting:d.hasClass("ui-unselecting")})})};this.refresh();this.selectees=c.addClass("ui-selectee");this._mouseInit();this.helper=a(document.createElement("div")).css({border:"1px dotted black"}).addClass("ui-selectable-helper")},destroy:function(){this.element.removeClass("ui-selectable ui-selectable-disabled").removeData("selectable").unbind(".selectable");this._mouseDestroy()},_mouseStart:function(d){var b=this;this.opos=[d.pageX,d.pageY];if(this.options.disabled){return}var c=this.options;this.selectees=a(c.filter,this.element[0]);this._trigger("start",d);a(c.appendTo).append(this.helper);this.helper.css({"z-index":100,position:"absolute",left:d.clientX,top:d.clientY,width:0,height:0});if(c.autoRefresh){this.refresh()}this.selectees.filter(".ui-selected").each(function(){var e=a.data(this,"selectable-item");e.startselected=true;if(!d.metaKey){e.$element.removeClass("ui-selected");e.selected=false;e.$element.addClass("ui-unselecting");e.unselecting=true;b._trigger("unselecting",d,{unselecting:e.element})}});a(d.target).parents().andSelf().each(function(){var e=a.data(this,"selectable-item");if(e){e.$element.removeClass("ui-unselecting").addClass("ui-selecting");e.unselecting=false;e.selecting=true;e.selected=true;b._trigger("selecting",d,{selecting:e.element});return false}})},_mouseDrag:function(i){var c=this;this.dragged=true;if(this.options.disabled){return}var e=this.options;var d=this.opos[0],h=this.opos[1],b=i.pageX,g=i.pageY;if(d>b){var f=b;b=d;d=f}if(h>g){var f=g;g=h;h=f}this.helper.css({left:d,top:h,width:b-d,height:g-h});this.selectees.each(function(){var j=a.data(this,"selectable-item");if(!j||j.element==c.element[0]){return}var k=false;if(e.tolerance=="touch"){k=(!(j.left>b||j.right<d||j.top>g||j.bottom<h))}else{if(e.tolerance=="fit"){k=(j.left>d&&j.right<b&&j.top>h&&j.bottom<g)}}if(k){if(j.selected){j.$element.removeClass("ui-selected");j.selected=false}if(j.unselecting){j.$element.removeClass("ui-unselecting");j.unselecting=false}if(!j.selecting){j.$element.addClass("ui-selecting");j.selecting=true;c._trigger("selecting",i,{selecting:j.element})}}else{if(j.selecting){if(i.metaKey&&j.startselected){j.$element.removeClass("ui-selecting");j.selecting=false;j.$element.addClass("ui-selected");j.selected=true}else{j.$element.removeClass("ui-selecting");j.selecting=false;if(j.startselected){j.$element.addClass("ui-unselecting");j.unselecting=true}c._trigger("unselecting",i,{unselecting:j.element})}}if(j.selected){if(!i.metaKey&&!j.startselected){j.$element.removeClass("ui-selected");j.selected=false;j.$element.addClass("ui-unselecting");j.unselecting=true;c._trigger("unselecting",i,{unselecting:j.element})}}}});return false},_mouseStop:function(d){var b=this;this.dragged=false;var c=this.options;a(".ui-unselecting",this.element[0]).each(function(){var e=a.data(this,"selectable-item");e.$element.removeClass("ui-unselecting");e.unselecting=false;e.startselected=false;b._trigger("unselected",d,{unselected:e.element})});a(".ui-selecting",this.element[0]).each(function(){var e=a.data(this,"selectable-item");e.$element.removeClass("ui-selecting").addClass("ui-selected");e.selecting=false;e.selected=true;e.startselected=true;b._trigger("selected",d,{selected:e.element})});this._trigger("stop",d);this.helper.remove();return false}}));a.extend(a.ui.selectable,{version:"1.7.2",defaults:{appendTo:"body",autoRefresh:true,cancel:":input,option",delay:0,distance:0,filter:"*",tolerance:"touch"}})})(jQuery);;/*
 * jQuery UI Sortable 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Sortables
 *
 * Depends:
 *	ui.core.js
 */
(function(a){a.widget("ui.sortable",a.extend({},a.ui.mouse,{_init:function(){var b=this.options;this.containerCache={};this.element.addClass("ui-sortable");this.refresh();this.floating=this.items.length?(/left|right/).test(this.items[0].item.css("float")):false;this.offset=this.element.offset();this._mouseInit()},destroy:function(){this.element.removeClass("ui-sortable ui-sortable-disabled").removeData("sortable").unbind(".sortable");this._mouseDestroy();for(var b=this.items.length-1;b>=0;b--){this.items[b].item.removeData("sortable-item")}},_mouseCapture:function(e,f){if(this.reverting){return false}if(this.options.disabled||this.options.type=="static"){return false}this._refreshItems(e);var d=null,c=this,b=a(e.target).parents().each(function(){if(a.data(this,"sortable-item")==c){d=a(this);return false}});if(a.data(e.target,"sortable-item")==c){d=a(e.target)}if(!d){return false}if(this.options.handle&&!f){var g=false;a(this.options.handle,d).find("*").andSelf().each(function(){if(this==e.target){g=true}});if(!g){return false}}this.currentItem=d;this._removeCurrentsFromItems();return true},_mouseStart:function(e,f,b){var g=this.options,c=this;this.currentContainer=this;this.refreshPositions();this.helper=this._createHelper(e);this._cacheHelperProportions();this._cacheMargins();this.scrollParent=this.helper.scrollParent();this.offset=this.currentItem.offset();this.offset={top:this.offset.top-this.margins.top,left:this.offset.left-this.margins.left};this.helper.css("position","absolute");this.cssPosition=this.helper.css("position");a.extend(this.offset,{click:{left:e.pageX-this.offset.left,top:e.pageY-this.offset.top},parent:this._getParentOffset(),relative:this._getRelativeOffset()});this.originalPosition=this._generatePosition(e);this.originalPageX=e.pageX;this.originalPageY=e.pageY;if(g.cursorAt){this._adjustOffsetFromHelper(g.cursorAt)}this.domPosition={prev:this.currentItem.prev()[0],parent:this.currentItem.parent()[0]};if(this.helper[0]!=this.currentItem[0]){this.currentItem.hide()}this._createPlaceholder();if(g.containment){this._setContainment()}if(g.cursor){if(a("body").css("cursor")){this._storedCursor=a("body").css("cursor")}a("body").css("cursor",g.cursor)}if(g.opacity){if(this.helper.css("opacity")){this._storedOpacity=this.helper.css("opacity")}this.helper.css("opacity",g.opacity)}if(g.zIndex){if(this.helper.css("zIndex")){this._storedZIndex=this.helper.css("zIndex")}this.helper.css("zIndex",g.zIndex)}if(this.scrollParent[0]!=document&&this.scrollParent[0].tagName!="HTML"){this.overflowOffset=this.scrollParent.offset()}this._trigger("start",e,this._uiHash());if(!this._preserveHelperProportions){this._cacheHelperProportions()}if(!b){for(var d=this.containers.length-1;d>=0;d--){this.containers[d]._trigger("activate",e,c._uiHash(this))}}if(a.ui.ddmanager){a.ui.ddmanager.current=this}if(a.ui.ddmanager&&!g.dropBehaviour){a.ui.ddmanager.prepareOffsets(this,e)}this.dragging=true;this.helper.addClass("ui-sortable-helper");this._mouseDrag(e);return true},_mouseDrag:function(f){this.position=this._generatePosition(f);this.positionAbs=this._convertPositionTo("absolute");if(!this.lastPositionAbs){this.lastPositionAbs=this.positionAbs}if(this.options.scroll){var g=this.options,b=false;if(this.scrollParent[0]!=document&&this.scrollParent[0].tagName!="HTML"){if((this.overflowOffset.top+this.scrollParent[0].offsetHeight)-f.pageY<g.scrollSensitivity){this.scrollParent[0].scrollTop=b=this.scrollParent[0].scrollTop+g.scrollSpeed}else{if(f.pageY-this.overflowOffset.top<g.scrollSensitivity){this.scrollParent[0].scrollTop=b=this.scrollParent[0].scrollTop-g.scrollSpeed}}if((this.overflowOffset.left+this.scrollParent[0].offsetWidth)-f.pageX<g.scrollSensitivity){this.scrollParent[0].scrollLeft=b=this.scrollParent[0].scrollLeft+g.scrollSpeed}else{if(f.pageX-this.overflowOffset.left<g.scrollSensitivity){this.scrollParent[0].scrollLeft=b=this.scrollParent[0].scrollLeft-g.scrollSpeed}}}else{if(f.pageY-a(document).scrollTop()<g.scrollSensitivity){b=a(document).scrollTop(a(document).scrollTop()-g.scrollSpeed)}else{if(a(window).height()-(f.pageY-a(document).scrollTop())<g.scrollSensitivity){b=a(document).scrollTop(a(document).scrollTop()+g.scrollSpeed)}}if(f.pageX-a(document).scrollLeft()<g.scrollSensitivity){b=a(document).scrollLeft(a(document).scrollLeft()-g.scrollSpeed)}else{if(a(window).width()-(f.pageX-a(document).scrollLeft())<g.scrollSensitivity){b=a(document).scrollLeft(a(document).scrollLeft()+g.scrollSpeed)}}}if(b!==false&&a.ui.ddmanager&&!g.dropBehaviour){a.ui.ddmanager.prepareOffsets(this,f)}}this.positionAbs=this._convertPositionTo("absolute");if(!this.options.axis||this.options.axis!="y"){this.helper[0].style.left=this.position.left+"px"}if(!this.options.axis||this.options.axis!="x"){this.helper[0].style.top=this.position.top+"px"}for(var d=this.items.length-1;d>=0;d--){var e=this.items[d],c=e.item[0],h=this._intersectsWithPointer(e);if(!h){continue}if(c!=this.currentItem[0]&&this.placeholder[h==1?"next":"prev"]()[0]!=c&&!a.ui.contains(this.placeholder[0],c)&&(this.options.type=="semi-dynamic"?!a.ui.contains(this.element[0],c):true)){this.direction=h==1?"down":"up";if(this.options.tolerance=="pointer"||this._intersectsWithSides(e)){this._rearrange(f,e)}else{break}this._trigger("change",f,this._uiHash());break}}this._contactContainers(f);if(a.ui.ddmanager){a.ui.ddmanager.drag(this,f)}this._trigger("sort",f,this._uiHash());this.lastPositionAbs=this.positionAbs;return false},_mouseStop:function(c,d){if(!c){return}if(a.ui.ddmanager&&!this.options.dropBehaviour){a.ui.ddmanager.drop(this,c)}if(this.options.revert){var b=this;var e=b.placeholder.offset();b.reverting=true;a(this.helper).animate({left:e.left-this.offset.parent.left-b.margins.left+(this.offsetParent[0]==document.body?0:this.offsetParent[0].scrollLeft),top:e.top-this.offset.parent.top-b.margins.top+(this.offsetParent[0]==document.body?0:this.offsetParent[0].scrollTop)},parseInt(this.options.revert,10)||500,function(){b._clear(c)})}else{this._clear(c,d)}return false},cancel:function(){var b=this;if(this.dragging){this._mouseUp();if(this.options.helper=="original"){this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper")}else{this.currentItem.show()}for(var c=this.containers.length-1;c>=0;c--){this.containers[c]._trigger("deactivate",null,b._uiHash(this));if(this.containers[c].containerCache.over){this.containers[c]._trigger("out",null,b._uiHash(this));this.containers[c].containerCache.over=0}}}if(this.placeholder[0].parentNode){this.placeholder[0].parentNode.removeChild(this.placeholder[0])}if(this.options.helper!="original"&&this.helper&&this.helper[0].parentNode){this.helper.remove()}a.extend(this,{helper:null,dragging:false,reverting:false,_noFinalSort:null});if(this.domPosition.prev){a(this.domPosition.prev).after(this.currentItem)}else{a(this.domPosition.parent).prepend(this.currentItem)}return true},serialize:function(d){var b=this._getItemsAsjQuery(d&&d.connected);var c=[];d=d||{};a(b).each(function(){var e=(a(d.item||this).attr(d.attribute||"id")||"").match(d.expression||(/(.+)[-=_](.+)/));if(e){c.push((d.key||e[1]+"[]")+"="+(d.key&&d.expression?e[1]:e[2]))}});return c.join("&")},toArray:function(d){var b=this._getItemsAsjQuery(d&&d.connected);var c=[];d=d||{};b.each(function(){c.push(a(d.item||this).attr(d.attribute||"id")||"")});return c},_intersectsWith:function(m){var e=this.positionAbs.left,d=e+this.helperProportions.width,k=this.positionAbs.top,j=k+this.helperProportions.height;var f=m.left,c=f+m.width,n=m.top,i=n+m.height;var o=this.offset.click.top,h=this.offset.click.left;var g=(k+o)>n&&(k+o)<i&&(e+h)>f&&(e+h)<c;if(this.options.tolerance=="pointer"||this.options.forcePointerForContainers||(this.options.tolerance!="pointer"&&this.helperProportions[this.floating?"width":"height"]>m[this.floating?"width":"height"])){return g}else{return(f<e+(this.helperProportions.width/2)&&d-(this.helperProportions.width/2)<c&&n<k+(this.helperProportions.height/2)&&j-(this.helperProportions.height/2)<i)}},_intersectsWithPointer:function(d){var e=a.ui.isOverAxis(this.positionAbs.top+this.offset.click.top,d.top,d.height),c=a.ui.isOverAxis(this.positionAbs.left+this.offset.click.left,d.left,d.width),g=e&&c,b=this._getDragVerticalDirection(),f=this._getDragHorizontalDirection();if(!g){return false}return this.floating?(((f&&f=="right")||b=="down")?2:1):(b&&(b=="down"?2:1))},_intersectsWithSides:function(e){var c=a.ui.isOverAxis(this.positionAbs.top+this.offset.click.top,e.top+(e.height/2),e.height),d=a.ui.isOverAxis(this.positionAbs.left+this.offset.click.left,e.left+(e.width/2),e.width),b=this._getDragVerticalDirection(),f=this._getDragHorizontalDirection();if(this.floating&&f){return((f=="right"&&d)||(f=="left"&&!d))}else{return b&&((b=="down"&&c)||(b=="up"&&!c))}},_getDragVerticalDirection:function(){var b=this.positionAbs.top-this.lastPositionAbs.top;return b!=0&&(b>0?"down":"up")},_getDragHorizontalDirection:function(){var b=this.positionAbs.left-this.lastPositionAbs.left;return b!=0&&(b>0?"right":"left")},refresh:function(b){this._refreshItems(b);this.refreshPositions()},_connectWith:function(){var b=this.options;return b.connectWith.constructor==String?[b.connectWith]:b.connectWith},_getItemsAsjQuery:function(b){var l=this;var g=[];var e=[];var h=this._connectWith();if(h&&b){for(var d=h.length-1;d>=0;d--){var k=a(h[d]);for(var c=k.length-1;c>=0;c--){var f=a.data(k[c],"sortable");if(f&&f!=this&&!f.options.disabled){e.push([a.isFunction(f.options.items)?f.options.items.call(f.element):a(f.options.items,f.element).not(".ui-sortable-helper"),f])}}}}e.push([a.isFunction(this.options.items)?this.options.items.call(this.element,null,{options:this.options,item:this.currentItem}):a(this.options.items,this.element).not(".ui-sortable-helper"),this]);for(var d=e.length-1;d>=0;d--){e[d][0].each(function(){g.push(this)})}return a(g)},_removeCurrentsFromItems:function(){var d=this.currentItem.find(":data(sortable-item)");for(var c=0;c<this.items.length;c++){for(var b=0;b<d.length;b++){if(d[b]==this.items[c].item[0]){this.items.splice(c,1)}}}},_refreshItems:function(b){this.items=[];this.containers=[this];var h=this.items;var p=this;var f=[[a.isFunction(this.options.items)?this.options.items.call(this.element[0],b,{item:this.currentItem}):a(this.options.items,this.element),this]];var l=this._connectWith();if(l){for(var e=l.length-1;e>=0;e--){var m=a(l[e]);for(var d=m.length-1;d>=0;d--){var g=a.data(m[d],"sortable");if(g&&g!=this&&!g.options.disabled){f.push([a.isFunction(g.options.items)?g.options.items.call(g.element[0],b,{item:this.currentItem}):a(g.options.items,g.element),g]);this.containers.push(g)}}}}for(var e=f.length-1;e>=0;e--){var k=f[e][1];var c=f[e][0];for(var d=0,n=c.length;d<n;d++){var o=a(c[d]);o.data("sortable-item",k);h.push({item:o,instance:k,width:0,height:0,left:0,top:0})}}},refreshPositions:function(b){if(this.offsetParent&&this.helper){this.offset.parent=this._getParentOffset()}for(var d=this.items.length-1;d>=0;d--){var e=this.items[d];if(e.instance!=this.currentContainer&&this.currentContainer&&e.item[0]!=this.currentItem[0]){continue}var c=this.options.toleranceElement?a(this.options.toleranceElement,e.item):e.item;if(!b){e.width=c.outerWidth();e.height=c.outerHeight()}var f=c.offset();e.left=f.left;e.top=f.top}if(this.options.custom&&this.options.custom.refreshContainers){this.options.custom.refreshContainers.call(this)}else{for(var d=this.containers.length-1;d>=0;d--){var f=this.containers[d].element.offset();this.containers[d].containerCache.left=f.left;this.containers[d].containerCache.top=f.top;this.containers[d].containerCache.width=this.containers[d].element.outerWidth();this.containers[d].containerCache.height=this.containers[d].element.outerHeight()}}},_createPlaceholder:function(d){var b=d||this,e=b.options;if(!e.placeholder||e.placeholder.constructor==String){var c=e.placeholder;e.placeholder={element:function(){var f=a(document.createElement(b.currentItem[0].nodeName)).addClass(c||b.currentItem[0].className+" ui-sortable-placeholder").removeClass("ui-sortable-helper")[0];if(!c){f.style.visibility="hidden"}return f},update:function(f,g){if(c&&!e.forcePlaceholderSize){return}if(!g.height()){g.height(b.currentItem.innerHeight()-parseInt(b.currentItem.css("paddingTop")||0,10)-parseInt(b.currentItem.css("paddingBottom")||0,10))}if(!g.width()){g.width(b.currentItem.innerWidth()-parseInt(b.currentItem.css("paddingLeft")||0,10)-parseInt(b.currentItem.css("paddingRight")||0,10))}}}}b.placeholder=a(e.placeholder.element.call(b.element,b.currentItem));b.currentItem.after(b.placeholder);e.placeholder.update(b,b.placeholder)},_contactContainers:function(d){for(var c=this.containers.length-1;c>=0;c--){if(this._intersectsWith(this.containers[c].containerCache)){if(!this.containers[c].containerCache.over){if(this.currentContainer!=this.containers[c]){var h=10000;var g=null;var e=this.positionAbs[this.containers[c].floating?"left":"top"];for(var b=this.items.length-1;b>=0;b--){if(!a.ui.contains(this.containers[c].element[0],this.items[b].item[0])){continue}var f=this.items[b][this.containers[c].floating?"left":"top"];if(Math.abs(f-e)<h){h=Math.abs(f-e);g=this.items[b]}}if(!g&&!this.options.dropOnEmpty){continue}this.currentContainer=this.containers[c];g?this._rearrange(d,g,null,true):this._rearrange(d,null,this.containers[c].element,true);this._trigger("change",d,this._uiHash());this.containers[c]._trigger("change",d,this._uiHash(this));this.options.placeholder.update(this.currentContainer,this.placeholder)}this.containers[c]._trigger("over",d,this._uiHash(this));this.containers[c].containerCache.over=1}}else{if(this.containers[c].containerCache.over){this.containers[c]._trigger("out",d,this._uiHash(this));this.containers[c].containerCache.over=0}}}},_createHelper:function(c){var d=this.options;var b=a.isFunction(d.helper)?a(d.helper.apply(this.element[0],[c,this.currentItem])):(d.helper=="clone"?this.currentItem.clone():this.currentItem);if(!b.parents("body").length){a(d.appendTo!="parent"?d.appendTo:this.currentItem[0].parentNode)[0].appendChild(b[0])}if(b[0]==this.currentItem[0]){this._storedCSS={width:this.currentItem[0].style.width,height:this.currentItem[0].style.height,position:this.currentItem.css("position"),top:this.currentItem.css("top"),left:this.currentItem.css("left")}}if(b[0].style.width==""||d.forceHelperSize){b.width(this.currentItem.width())}if(b[0].style.height==""||d.forceHelperSize){b.height(this.currentItem.height())}return b},_adjustOffsetFromHelper:function(b){if(b.left!=undefined){this.offset.click.left=b.left+this.margins.left}if(b.right!=undefined){this.offset.click.left=this.helperProportions.width-b.right+this.margins.left}if(b.top!=undefined){this.offset.click.top=b.top+this.margins.top}if(b.bottom!=undefined){this.offset.click.top=this.helperProportions.height-b.bottom+this.margins.top}},_getParentOffset:function(){this.offsetParent=this.helper.offsetParent();var b=this.offsetParent.offset();if(this.cssPosition=="absolute"&&this.scrollParent[0]!=document&&a.ui.contains(this.scrollParent[0],this.offsetParent[0])){b.left+=this.scrollParent.scrollLeft();b.top+=this.scrollParent.scrollTop()}if((this.offsetParent[0]==document.body)||(this.offsetParent[0].tagName&&this.offsetParent[0].tagName.toLowerCase()=="html"&&a.browser.msie)){b={top:0,left:0}}return{top:b.top+(parseInt(this.offsetParent.css("borderTopWidth"),10)||0),left:b.left+(parseInt(this.offsetParent.css("borderLeftWidth"),10)||0)}},_getRelativeOffset:function(){if(this.cssPosition=="relative"){var b=this.currentItem.position();return{top:b.top-(parseInt(this.helper.css("top"),10)||0)+this.scrollParent.scrollTop(),left:b.left-(parseInt(this.helper.css("left"),10)||0)+this.scrollParent.scrollLeft()}}else{return{top:0,left:0}}},_cacheMargins:function(){this.margins={left:(parseInt(this.currentItem.css("marginLeft"),10)||0),top:(parseInt(this.currentItem.css("marginTop"),10)||0)}},_cacheHelperProportions:function(){this.helperProportions={width:this.helper.outerWidth(),height:this.helper.outerHeight()}},_setContainment:function(){var e=this.options;if(e.containment=="parent"){e.containment=this.helper[0].parentNode}if(e.containment=="document"||e.containment=="window"){this.containment=[0-this.offset.relative.left-this.offset.parent.left,0-this.offset.relative.top-this.offset.parent.top,a(e.containment=="document"?document:window).width()-this.helperProportions.width-this.margins.left,(a(e.containment=="document"?document:window).height()||document.body.parentNode.scrollHeight)-this.helperProportions.height-this.margins.top]}if(!(/^(document|window|parent)$/).test(e.containment)){var c=a(e.containment)[0];var d=a(e.containment).offset();var b=(a(c).css("overflow")!="hidden");this.containment=[d.left+(parseInt(a(c).css("borderLeftWidth"),10)||0)+(parseInt(a(c).css("paddingLeft"),10)||0)-this.margins.left,d.top+(parseInt(a(c).css("borderTopWidth"),10)||0)+(parseInt(a(c).css("paddingTop"),10)||0)-this.margins.top,d.left+(b?Math.max(c.scrollWidth,c.offsetWidth):c.offsetWidth)-(parseInt(a(c).css("borderLeftWidth"),10)||0)-(parseInt(a(c).css("paddingRight"),10)||0)-this.helperProportions.width-this.margins.left,d.top+(b?Math.max(c.scrollHeight,c.offsetHeight):c.offsetHeight)-(parseInt(a(c).css("borderTopWidth"),10)||0)-(parseInt(a(c).css("paddingBottom"),10)||0)-this.helperProportions.height-this.margins.top]}},_convertPositionTo:function(f,h){if(!h){h=this.position}var c=f=="absolute"?1:-1;var e=this.options,b=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&a.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,g=(/(html|body)/i).test(b[0].tagName);return{top:(h.top+this.offset.relative.top*c+this.offset.parent.top*c-(a.browser.safari&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollTop():(g?0:b.scrollTop()))*c)),left:(h.left+this.offset.relative.left*c+this.offset.parent.left*c-(a.browser.safari&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():g?0:b.scrollLeft())*c))}},_generatePosition:function(e){var h=this.options,b=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&a.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,i=(/(html|body)/i).test(b[0].tagName);if(this.cssPosition=="relative"&&!(this.scrollParent[0]!=document&&this.scrollParent[0]!=this.offsetParent[0])){this.offset.relative=this._getRelativeOffset()}var d=e.pageX;var c=e.pageY;if(this.originalPosition){if(this.containment){if(e.pageX-this.offset.click.left<this.containment[0]){d=this.containment[0]+this.offset.click.left}if(e.pageY-this.offset.click.top<this.containment[1]){c=this.containment[1]+this.offset.click.top}if(e.pageX-this.offset.click.left>this.containment[2]){d=this.containment[2]+this.offset.click.left}if(e.pageY-this.offset.click.top>this.containment[3]){c=this.containment[3]+this.offset.click.top}}if(h.grid){var g=this.originalPageY+Math.round((c-this.originalPageY)/h.grid[1])*h.grid[1];c=this.containment?(!(g-this.offset.click.top<this.containment[1]||g-this.offset.click.top>this.containment[3])?g:(!(g-this.offset.click.top<this.containment[1])?g-h.grid[1]:g+h.grid[1])):g;var f=this.originalPageX+Math.round((d-this.originalPageX)/h.grid[0])*h.grid[0];d=this.containment?(!(f-this.offset.click.left<this.containment[0]||f-this.offset.click.left>this.containment[2])?f:(!(f-this.offset.click.left<this.containment[0])?f-h.grid[0]:f+h.grid[0])):f}}return{top:(c-this.offset.click.top-this.offset.relative.top-this.offset.parent.top+(a.browser.safari&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollTop():(i?0:b.scrollTop())))),left:(d-this.offset.click.left-this.offset.relative.left-this.offset.parent.left+(a.browser.safari&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():i?0:b.scrollLeft())))}},_rearrange:function(g,f,c,e){c?c[0].appendChild(this.placeholder[0]):f.item[0].parentNode.insertBefore(this.placeholder[0],(this.direction=="down"?f.item[0]:f.item[0].nextSibling));this.counter=this.counter?++this.counter:1;var d=this,b=this.counter;window.setTimeout(function(){if(b==d.counter){d.refreshPositions(!e)}},0)},_clear:function(d,e){this.reverting=false;var f=[],b=this;if(!this._noFinalSort&&this.currentItem[0].parentNode){this.placeholder.before(this.currentItem)}this._noFinalSort=null;if(this.helper[0]==this.currentItem[0]){for(var c in this._storedCSS){if(this._storedCSS[c]=="auto"||this._storedCSS[c]=="static"){this._storedCSS[c]=""}}this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper")}else{this.currentItem.show()}if(this.fromOutside&&!e){f.push(function(g){this._trigger("receive",g,this._uiHash(this.fromOutside))})}if((this.fromOutside||this.domPosition.prev!=this.currentItem.prev().not(".ui-sortable-helper")[0]||this.domPosition.parent!=this.currentItem.parent()[0])&&!e){f.push(function(g){this._trigger("update",g,this._uiHash())})}if(!a.ui.contains(this.element[0],this.currentItem[0])){if(!e){f.push(function(g){this._trigger("remove",g,this._uiHash())})}for(var c=this.containers.length-1;c>=0;c--){if(a.ui.contains(this.containers[c].element[0],this.currentItem[0])&&!e){f.push((function(g){return function(h){g._trigger("receive",h,this._uiHash(this))}}).call(this,this.containers[c]));f.push((function(g){return function(h){g._trigger("update",h,this._uiHash(this))}}).call(this,this.containers[c]))}}}for(var c=this.containers.length-1;c>=0;c--){if(!e){f.push((function(g){return function(h){g._trigger("deactivate",h,this._uiHash(this))}}).call(this,this.containers[c]))}if(this.containers[c].containerCache.over){f.push((function(g){return function(h){g._trigger("out",h,this._uiHash(this))}}).call(this,this.containers[c]));this.containers[c].containerCache.over=0}}if(this._storedCursor){a("body").css("cursor",this._storedCursor)}if(this._storedOpacity){this.helper.css("opacity",this._storedOpacity)}if(this._storedZIndex){this.helper.css("zIndex",this._storedZIndex=="auto"?"":this._storedZIndex)}this.dragging=false;if(this.cancelHelperRemoval){if(!e){this._trigger("beforeStop",d,this._uiHash());for(var c=0;c<f.length;c++){f[c].call(this,d)}this._trigger("stop",d,this._uiHash())}return false}if(!e){this._trigger("beforeStop",d,this._uiHash())}this.placeholder[0].parentNode.removeChild(this.placeholder[0]);if(this.helper[0]!=this.currentItem[0]){this.helper.remove()}this.helper=null;if(!e){for(var c=0;c<f.length;c++){f[c].call(this,d)}this._trigger("stop",d,this._uiHash())}this.fromOutside=false;return true},_trigger:function(){if(a.widget.prototype._trigger.apply(this,arguments)===false){this.cancel()}},_uiHash:function(c){var b=c||this;return{helper:b.helper,placeholder:b.placeholder||a([]),position:b.position,absolutePosition:b.positionAbs,offset:b.positionAbs,item:b.currentItem,sender:c?c.element:null}}}));a.extend(a.ui.sortable,{getter:"serialize toArray",version:"1.7.2",eventPrefix:"sort",defaults:{appendTo:"parent",axis:false,cancel:":input,option",connectWith:false,containment:false,cursor:"auto",cursorAt:false,delay:0,distance:1,dropOnEmpty:true,forcePlaceholderSize:false,forceHelperSize:false,grid:false,handle:false,helper:"original",items:"> *",opacity:false,placeholder:false,revert:false,scroll:true,scrollSensitivity:20,scrollSpeed:20,scope:"default",tolerance:"intersect",zIndex:1000}})})(jQuery);;/*
 * jQuery UI Accordion 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Accordion
 *
 * Depends:
 *	ui.core.js
 */
(function(a){a.widget("ui.accordion",{_init:function(){var d=this.options,b=this;this.running=0;if(d.collapsible==a.ui.accordion.defaults.collapsible&&d.alwaysOpen!=a.ui.accordion.defaults.alwaysOpen){d.collapsible=!d.alwaysOpen}if(d.navigation){var c=this.element.find("a").filter(d.navigationFilter);if(c.length){if(c.filter(d.header).length){this.active=c}else{this.active=c.parent().parent().prev();c.addClass("ui-accordion-content-active")}}}this.element.addClass("ui-accordion ui-widget ui-helper-reset");if(this.element[0].nodeName=="UL"){this.element.children("li").addClass("ui-accordion-li-fix")}this.headers=this.element.find(d.header).addClass("ui-accordion-header ui-helper-reset ui-state-default ui-corner-all").bind("mouseenter.accordion",function(){a(this).addClass("ui-state-hover")}).bind("mouseleave.accordion",function(){a(this).removeClass("ui-state-hover")}).bind("focus.accordion",function(){a(this).addClass("ui-state-focus")}).bind("blur.accordion",function(){a(this).removeClass("ui-state-focus")});this.headers.next().addClass("ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom");this.active=this._findActive(this.active||d.active).toggleClass("ui-state-default").toggleClass("ui-state-active").toggleClass("ui-corner-all").toggleClass("ui-corner-top");this.active.next().addClass("ui-accordion-content-active");a("<span/>").addClass("ui-icon "+d.icons.header).prependTo(this.headers);this.active.find(".ui-icon").toggleClass(d.icons.header).toggleClass(d.icons.headerSelected);if(a.browser.msie){this.element.find("a").css("zoom","1")}this.resize();this.element.attr("role","tablist");this.headers.attr("role","tab").bind("keydown",function(e){return b._keydown(e)}).next().attr("role","tabpanel");this.headers.not(this.active||"").attr("aria-expanded","false").attr("tabIndex","-1").next().hide();if(!this.active.length){this.headers.eq(0).attr("tabIndex","0")}else{this.active.attr("aria-expanded","true").attr("tabIndex","0")}if(!a.browser.safari){this.headers.find("a").attr("tabIndex","-1")}if(d.event){this.headers.bind((d.event)+".accordion",function(e){return b._clickHandler.call(b,e,this)})}},destroy:function(){var c=this.options;this.element.removeClass("ui-accordion ui-widget ui-helper-reset").removeAttr("role").unbind(".accordion").removeData("accordion");this.headers.unbind(".accordion").removeClass("ui-accordion-header ui-helper-reset ui-state-default ui-corner-all ui-state-active ui-corner-top").removeAttr("role").removeAttr("aria-expanded").removeAttr("tabindex");this.headers.find("a").removeAttr("tabindex");this.headers.children(".ui-icon").remove();var b=this.headers.next().css("display","").removeAttr("role").removeClass("ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content ui-accordion-content-active");if(c.autoHeight||c.fillHeight){b.css("height","")}},_setData:function(b,c){if(b=="alwaysOpen"){b="collapsible";c=!c}a.widget.prototype._setData.apply(this,arguments)},_keydown:function(e){var g=this.options,f=a.ui.keyCode;if(g.disabled||e.altKey||e.ctrlKey){return}var d=this.headers.length;var b=this.headers.index(e.target);var c=false;switch(e.keyCode){case f.RIGHT:case f.DOWN:c=this.headers[(b+1)%d];break;case f.LEFT:case f.UP:c=this.headers[(b-1+d)%d];break;case f.SPACE:case f.ENTER:return this._clickHandler({target:e.target},e.target)}if(c){a(e.target).attr("tabIndex","-1");a(c).attr("tabIndex","0");c.focus();return false}return true},resize:function(){var e=this.options,d;if(e.fillSpace){if(a.browser.msie){var b=this.element.parent().css("overflow");this.element.parent().css("overflow","hidden")}d=this.element.parent().height();if(a.browser.msie){this.element.parent().css("overflow",b)}this.headers.each(function(){d-=a(this).outerHeight()});var c=0;this.headers.next().each(function(){c=Math.max(c,a(this).innerHeight()-a(this).height())}).height(Math.max(0,d-c)).css("overflow","auto")}else{if(e.autoHeight){d=0;this.headers.next().each(function(){d=Math.max(d,a(this).outerHeight())}).height(d)}}},activate:function(b){var c=this._findActive(b)[0];this._clickHandler({target:c},c)},_findActive:function(b){return b?typeof b=="number"?this.headers.filter(":eq("+b+")"):this.headers.not(this.headers.not(b)):b===false?a([]):this.headers.filter(":eq(0)")},_clickHandler:function(b,f){var d=this.options;if(d.disabled){return false}if(!b.target&&d.collapsible){this.active.removeClass("ui-state-active ui-corner-top").addClass("ui-state-default ui-corner-all").find(".ui-icon").removeClass(d.icons.headerSelected).addClass(d.icons.header);this.active.next().addClass("ui-accordion-content-active");var h=this.active.next(),e={options:d,newHeader:a([]),oldHeader:d.active,newContent:a([]),oldContent:h},c=(this.active=a([]));this._toggle(c,h,e);return false}var g=a(b.currentTarget||f);var i=g[0]==this.active[0];if(this.running||(!d.collapsible&&i)){return false}this.active.removeClass("ui-state-active ui-corner-top").addClass("ui-state-default ui-corner-all").find(".ui-icon").removeClass(d.icons.headerSelected).addClass(d.icons.header);this.active.next().addClass("ui-accordion-content-active");if(!i){g.removeClass("ui-state-default ui-corner-all").addClass("ui-state-active ui-corner-top").find(".ui-icon").removeClass(d.icons.header).addClass(d.icons.headerSelected);g.next().addClass("ui-accordion-content-active")}var c=g.next(),h=this.active.next(),e={options:d,newHeader:i&&d.collapsible?a([]):g,oldHeader:this.active,newContent:i&&d.collapsible?a([]):c.find("> *"),oldContent:h.find("> *")},j=this.headers.index(this.active[0])>this.headers.index(g[0]);this.active=i?a([]):g;this._toggle(c,h,e,i,j);return false},_toggle:function(b,i,g,j,k){var d=this.options,m=this;this.toShow=b;this.toHide=i;this.data=g;var c=function(){if(!m){return}return m._completed.apply(m,arguments)};this._trigger("changestart",null,this.data);this.running=i.size()===0?b.size():i.size();if(d.animated){var f={};if(d.collapsible&&j){f={toShow:a([]),toHide:i,complete:c,down:k,autoHeight:d.autoHeight||d.fillSpace}}else{f={toShow:b,toHide:i,complete:c,down:k,autoHeight:d.autoHeight||d.fillSpace}}if(!d.proxied){d.proxied=d.animated}if(!d.proxiedDuration){d.proxiedDuration=d.duration}d.animated=a.isFunction(d.proxied)?d.proxied(f):d.proxied;d.duration=a.isFunction(d.proxiedDuration)?d.proxiedDuration(f):d.proxiedDuration;var l=a.ui.accordion.animations,e=d.duration,h=d.animated;if(!l[h]){l[h]=function(n){this.slide(n,{easing:h,duration:e||700})}}l[h](f)}else{if(d.collapsible&&j){b.toggle()}else{i.hide();b.show()}c(true)}i.prev().attr("aria-expanded","false").attr("tabIndex","-1").blur();b.prev().attr("aria-expanded","true").attr("tabIndex","0").focus()},_completed:function(b){var c=this.options;this.running=b?0:--this.running;if(this.running){return}if(c.clearStyle){this.toShow.add(this.toHide).css({height:"",overflow:""})}this._trigger("change",null,this.data)}});a.extend(a.ui.accordion,{version:"1.7.2",defaults:{active:null,alwaysOpen:true,animated:"slide",autoHeight:true,clearStyle:false,collapsible:false,event:"click",fillSpace:false,header:"> li > :first-child,> :not(li):even",icons:{header:"ui-icon-triangle-1-e",headerSelected:"ui-icon-triangle-1-s"},navigation:false,navigationFilter:function(){return this.href.toLowerCase()==location.href.toLowerCase()}},animations:{slide:function(j,h){j=a.extend({easing:"swing",duration:300},j,h);if(!j.toHide.size()){j.toShow.animate({height:"show"},j);return}if(!j.toShow.size()){j.toHide.animate({height:"hide"},j);return}var c=j.toShow.css("overflow"),g,d={},f={},e=["height","paddingTop","paddingBottom"],b;var i=j.toShow;b=i[0].style.width;i.width(parseInt(i.parent().width(),10)-parseInt(i.css("paddingLeft"),10)-parseInt(i.css("paddingRight"),10)-(parseInt(i.css("borderLeftWidth"),10)||0)-(parseInt(i.css("borderRightWidth"),10)||0));a.each(e,function(k,m){f[m]="hide";var l=(""+a.css(j.toShow[0],m)).match(/^([\d+-.]+)(.*)$/);d[m]={value:l[1],unit:l[2]||"px"}});j.toShow.css({height:0,overflow:"hidden"}).show();j.toHide.filter(":hidden").each(j.complete).end().filter(":visible").animate(f,{step:function(k,l){if(l.prop=="height"){g=(l.now-l.start)/(l.end-l.start)}j.toShow[0].style[l.prop]=(g*d[l.prop].value)+d[l.prop].unit},duration:j.duration,easing:j.easing,complete:function(){if(!j.autoHeight){j.toShow.css("height","")}j.toShow.css("width",b);j.toShow.css({overflow:c});j.complete()}})},bounceslide:function(b){this.slide(b,{easing:b.down?"easeOutBounce":"swing",duration:b.down?1000:200})},easeslide:function(b){this.slide(b,{easing:"easeinout",duration:700})}}})})(jQuery);;/*
 * jQuery UI Dialog 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Dialog
 *
 * Depends:
 *	ui.core.js
 *	ui.draggable.js
 *	ui.resizable.js
 */
(function(c){var b={dragStart:"start.draggable",drag:"drag.draggable",dragStop:"stop.draggable",maxHeight:"maxHeight.resizable",minHeight:"minHeight.resizable",maxWidth:"maxWidth.resizable",minWidth:"minWidth.resizable",resizeStart:"start.resizable",resize:"drag.resizable",resizeStop:"stop.resizable"},a="ui-dialog ui-widget ui-widget-content ui-corner-all ";c.widget("ui.dialog",{_init:function(){this.originalTitle=this.element.attr("title");var l=this,m=this.options,j=m.title||this.originalTitle||"&nbsp;",e=c.ui.dialog.getTitleId(this.element),k=(this.uiDialog=c("<div/>")).appendTo(document.body).hide().addClass(a+m.dialogClass).css({position:"absolute",overflow:"hidden",zIndex:m.zIndex}).attr("tabIndex",-1).css("outline",0).keydown(function(n){(m.closeOnEscape&&n.keyCode&&n.keyCode==c.ui.keyCode.ESCAPE&&l.close(n))}).attr({role:"dialog","aria-labelledby":e}).mousedown(function(n){l.moveToTop(false,n)}),g=this.element.show().removeAttr("title").addClass("ui-dialog-content ui-widget-content").appendTo(k),f=(this.uiDialogTitlebar=c("<div></div>")).addClass("ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix").prependTo(k),i=c('<a href="#"/>').addClass("ui-dialog-titlebar-close ui-corner-all").attr("role","button").hover(function(){i.addClass("ui-state-hover")},function(){i.removeClass("ui-state-hover")}).focus(function(){i.addClass("ui-state-focus")}).blur(function(){i.removeClass("ui-state-focus")}).mousedown(function(n){n.stopPropagation()}).click(function(n){l.close(n);return false}).appendTo(f),h=(this.uiDialogTitlebarCloseText=c("<span/>")).addClass("ui-icon ui-icon-closethick").text(m.closeText).appendTo(i),d=c("<span/>").addClass("ui-dialog-title").attr("id",e).html(j).prependTo(f);f.find("*").add(f).disableSelection();(m.draggable&&c.fn.draggable&&this._makeDraggable());(m.resizable&&c.fn.resizable&&this._makeResizable());this._createButtons(m.buttons);this._isOpen=false;(m.bgiframe&&c.fn.bgiframe&&k.bgiframe());(m.autoOpen&&this.open())},destroy:function(){(this.overlay&&this.overlay.destroy());this.uiDialog.hide();this.element.unbind(".dialog").removeData("dialog").removeClass("ui-dialog-content ui-widget-content").hide().appendTo("body");this.uiDialog.remove();(this.originalTitle&&this.element.attr("title",this.originalTitle))},close:function(f){var d=this;if(false===d._trigger("beforeclose",f)){return}(d.overlay&&d.overlay.destroy());d.uiDialog.unbind("keypress.ui-dialog");(d.options.hide?d.uiDialog.hide(d.options.hide,function(){d._trigger("close",f)}):d.uiDialog.hide()&&d._trigger("close",f));c.ui.dialog.overlay.resize();d._isOpen=false;if(d.options.modal){var e=0;c(".ui-dialog").each(function(){if(this!=d.uiDialog[0]){e=Math.max(e,c(this).css("z-index"))}});c.ui.dialog.maxZ=e}},isOpen:function(){return this._isOpen},moveToTop:function(f,e){if((this.options.modal&&!f)||(!this.options.stack&&!this.options.modal)){return this._trigger("focus",e)}if(this.options.zIndex>c.ui.dialog.maxZ){c.ui.dialog.maxZ=this.options.zIndex}(this.overlay&&this.overlay.$el.css("z-index",c.ui.dialog.overlay.maxZ=++c.ui.dialog.maxZ));var d={scrollTop:this.element.attr("scrollTop"),scrollLeft:this.element.attr("scrollLeft")};this.uiDialog.css("z-index",++c.ui.dialog.maxZ);this.element.attr(d);this._trigger("focus",e)},open:function(){if(this._isOpen){return}var e=this.options,d=this.uiDialog;this.overlay=e.modal?new c.ui.dialog.overlay(this):null;(d.next().length&&d.appendTo("body"));this._size();this._position(e.position);d.show(e.show);this.moveToTop(true);(e.modal&&d.bind("keypress.ui-dialog",function(h){if(h.keyCode!=c.ui.keyCode.TAB){return}var g=c(":tabbable",this),i=g.filter(":first")[0],f=g.filter(":last")[0];if(h.target==f&&!h.shiftKey){setTimeout(function(){i.focus()},1)}else{if(h.target==i&&h.shiftKey){setTimeout(function(){f.focus()},1)}}}));c([]).add(d.find(".ui-dialog-content :tabbable:first")).add(d.find(".ui-dialog-buttonpane :tabbable:first")).add(d).filter(":first").focus();this._trigger("open");this._isOpen=true},_createButtons:function(g){var f=this,d=false,e=c("<div></div>").addClass("ui-dialog-buttonpane ui-widget-content ui-helper-clearfix");this.uiDialog.find(".ui-dialog-buttonpane").remove();(typeof g=="object"&&g!==null&&c.each(g,function(){return !(d=true)}));if(d){c.each(g,function(h,i){c('<button type="button"></button>').addClass("ui-state-default ui-corner-all").text(h).click(function(){i.apply(f.element[0],arguments)}).hover(function(){c(this).addClass("ui-state-hover")},function(){c(this).removeClass("ui-state-hover")}).focus(function(){c(this).addClass("ui-state-focus")}).blur(function(){c(this).removeClass("ui-state-focus")}).appendTo(e)});e.appendTo(this.uiDialog)}},_makeDraggable:function(){var d=this,f=this.options,e;this.uiDialog.draggable({cancel:".ui-dialog-content",handle:".ui-dialog-titlebar",containment:"document",start:function(){e=f.height;c(this).height(c(this).height()).addClass("ui-dialog-dragging");(f.dragStart&&f.dragStart.apply(d.element[0],arguments))},drag:function(){(f.drag&&f.drag.apply(d.element[0],arguments))},stop:function(){c(this).removeClass("ui-dialog-dragging").height(e);(f.dragStop&&f.dragStop.apply(d.element[0],arguments));c.ui.dialog.overlay.resize()}})},_makeResizable:function(g){g=(g===undefined?this.options.resizable:g);var d=this,f=this.options,e=typeof g=="string"?g:"n,e,s,w,se,sw,ne,nw";this.uiDialog.resizable({cancel:".ui-dialog-content",alsoResize:this.element,maxWidth:f.maxWidth,maxHeight:f.maxHeight,minWidth:f.minWidth,minHeight:f.minHeight,start:function(){c(this).addClass("ui-dialog-resizing");(f.resizeStart&&f.resizeStart.apply(d.element[0],arguments))},resize:function(){(f.resize&&f.resize.apply(d.element[0],arguments))},handles:e,stop:function(){c(this).removeClass("ui-dialog-resizing");f.height=c(this).height();f.width=c(this).width();(f.resizeStop&&f.resizeStop.apply(d.element[0],arguments));c.ui.dialog.overlay.resize()}}).find(".ui-resizable-se").addClass("ui-icon ui-icon-grip-diagonal-se")},_position:function(i){var e=c(window),f=c(document),g=f.scrollTop(),d=f.scrollLeft(),h=g;if(c.inArray(i,["center","top","right","bottom","left"])>=0){i=[i=="right"||i=="left"?i:"center",i=="top"||i=="bottom"?i:"middle"]}if(i.constructor!=Array){i=["center","middle"]}if(i[0].constructor==Number){d+=i[0]}else{switch(i[0]){case"left":d+=0;break;case"right":d+=e.width()-this.uiDialog.outerWidth();break;default:case"center":d+=(e.width()-this.uiDialog.outerWidth())/2}}if(i[1].constructor==Number){g+=i[1]}else{switch(i[1]){case"top":g+=0;break;case"bottom":g+=e.height()-this.uiDialog.outerHeight();break;default:case"middle":g+=(e.height()-this.uiDialog.outerHeight())/2}}g=Math.max(g,h);this.uiDialog.css({top:g,left:d})},_setData:function(e,f){(b[e]&&this.uiDialog.data(b[e],f));switch(e){case"buttons":this._createButtons(f);break;case"closeText":this.uiDialogTitlebarCloseText.text(f);break;case"dialogClass":this.uiDialog.removeClass(this.options.dialogClass).addClass(a+f);break;case"draggable":(f?this._makeDraggable():this.uiDialog.draggable("destroy"));break;case"height":this.uiDialog.height(f);break;case"position":this._position(f);break;case"resizable":var d=this.uiDialog,g=this.uiDialog.is(":data(resizable)");(g&&!f&&d.resizable("destroy"));(g&&typeof f=="string"&&d.resizable("option","handles",f));(g||this._makeResizable(f));break;case"title":c(".ui-dialog-title",this.uiDialogTitlebar).html(f||"&nbsp;");break;case"width":this.uiDialog.width(f);break}c.widget.prototype._setData.apply(this,arguments)},_size:function(){var e=this.options;this.element.css({height:0,minHeight:0,width:"auto"});var d=this.uiDialog.css({height:"auto",width:e.width}).height();this.element.css({minHeight:Math.max(e.minHeight-d,0),height:e.height=="auto"?"auto":Math.max(e.height-d,0)})}});c.extend(c.ui.dialog,{version:"1.7.2",defaults:{autoOpen:true,bgiframe:false,buttons:{},closeOnEscape:true,closeText:"close",dialogClass:"",draggable:true,hide:null,height:"auto",maxHeight:false,maxWidth:false,minHeight:150,minWidth:150,modal:false,position:"center",resizable:true,show:null,stack:true,title:"",width:300,zIndex:1000},getter:"isOpen",uuid:0,maxZ:0,getTitleId:function(d){return"ui-dialog-title-"+(d.attr("id")||++this.uuid)},overlay:function(d){this.$el=c.ui.dialog.overlay.create(d)}});c.extend(c.ui.dialog.overlay,{instances:[],maxZ:0,events:c.map("focus,mousedown,mouseup,keydown,keypress,click".split(","),function(d){return d+".dialog-overlay"}).join(" "),create:function(e){if(this.instances.length===0){setTimeout(function(){if(c.ui.dialog.overlay.instances.length){c(document).bind(c.ui.dialog.overlay.events,function(f){var g=c(f.target).parents(".ui-dialog").css("zIndex")||0;return(g>c.ui.dialog.overlay.maxZ)})}},1);c(document).bind("keydown.dialog-overlay",function(f){(e.options.closeOnEscape&&f.keyCode&&f.keyCode==c.ui.keyCode.ESCAPE&&e.close(f))});c(window).bind("resize.dialog-overlay",c.ui.dialog.overlay.resize)}var d=c("<div></div>").appendTo(document.body).addClass("ui-widget-overlay").css({width:this.width(),height:this.height()});(e.options.bgiframe&&c.fn.bgiframe&&d.bgiframe());this.instances.push(d);return d},destroy:function(d){this.instances.splice(c.inArray(this.instances,d),1);if(this.instances.length===0){c([document,window]).unbind(".dialog-overlay")}d.remove();var e=0;c.each(this.instances,function(){e=Math.max(e,this.css("z-index"))});this.maxZ=e},height:function(){if(c.browser.msie&&c.browser.version<7){var e=Math.max(document.documentElement.scrollHeight,document.body.scrollHeight);var d=Math.max(document.documentElement.offsetHeight,document.body.offsetHeight);if(e<d){return c(window).height()+"px"}else{return e+"px"}}else{return c(document).height()+"px"}},width:function(){if(c.browser.msie&&c.browser.version<7){var d=Math.max(document.documentElement.scrollWidth,document.body.scrollWidth);var e=Math.max(document.documentElement.offsetWidth,document.body.offsetWidth);if(d<e){return c(window).width()+"px"}else{return d+"px"}}else{return c(document).width()+"px"}},resize:function(){var d=c([]);c.each(c.ui.dialog.overlay.instances,function(){d=d.add(this)});d.css({width:0,height:0}).css({width:c.ui.dialog.overlay.width(),height:c.ui.dialog.overlay.height()})}});c.extend(c.ui.dialog.overlay.prototype,{destroy:function(){c.ui.dialog.overlay.destroy(this.$el)}})})(jQuery);;/*
 * jQuery UI Slider 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Slider
 *
 * Depends:
 *	ui.core.js
 */
(function(a){a.widget("ui.slider",a.extend({},a.ui.mouse,{_init:function(){var b=this,c=this.options;this._keySliding=false;this._handleIndex=null;this._detectOrientation();this._mouseInit();this.element.addClass("ui-slider ui-slider-"+this.orientation+" ui-widget ui-widget-content ui-corner-all");this.range=a([]);if(c.range){if(c.range===true){this.range=a("<div></div>");if(!c.values){c.values=[this._valueMin(),this._valueMin()]}if(c.values.length&&c.values.length!=2){c.values=[c.values[0],c.values[0]]}}else{this.range=a("<div></div>")}this.range.appendTo(this.element).addClass("ui-slider-range");if(c.range=="min"||c.range=="max"){this.range.addClass("ui-slider-range-"+c.range)}this.range.addClass("ui-widget-header")}if(a(".ui-slider-handle",this.element).length==0){a('<a href="#"></a>').appendTo(this.element).addClass("ui-slider-handle")}if(c.values&&c.values.length){while(a(".ui-slider-handle",this.element).length<c.values.length){a('<a href="#"></a>').appendTo(this.element).addClass("ui-slider-handle")}}this.handles=a(".ui-slider-handle",this.element).addClass("ui-state-default ui-corner-all");this.handle=this.handles.eq(0);this.handles.add(this.range).filter("a").click(function(d){d.preventDefault()}).hover(function(){if(!c.disabled){a(this).addClass("ui-state-hover")}},function(){a(this).removeClass("ui-state-hover")}).focus(function(){if(!c.disabled){a(".ui-slider .ui-state-focus").removeClass("ui-state-focus");a(this).addClass("ui-state-focus")}else{a(this).blur()}}).blur(function(){a(this).removeClass("ui-state-focus")});this.handles.each(function(d){a(this).data("index.ui-slider-handle",d)});this.handles.keydown(function(i){var f=true;var e=a(this).data("index.ui-slider-handle");if(b.options.disabled){return}switch(i.keyCode){case a.ui.keyCode.HOME:case a.ui.keyCode.END:case a.ui.keyCode.UP:case a.ui.keyCode.RIGHT:case a.ui.keyCode.DOWN:case a.ui.keyCode.LEFT:f=false;if(!b._keySliding){b._keySliding=true;a(this).addClass("ui-state-active");b._start(i,e)}break}var g,d,h=b._step();if(b.options.values&&b.options.values.length){g=d=b.values(e)}else{g=d=b.value()}switch(i.keyCode){case a.ui.keyCode.HOME:d=b._valueMin();break;case a.ui.keyCode.END:d=b._valueMax();break;case a.ui.keyCode.UP:case a.ui.keyCode.RIGHT:if(g==b._valueMax()){return}d=g+h;break;case a.ui.keyCode.DOWN:case a.ui.keyCode.LEFT:if(g==b._valueMin()){return}d=g-h;break}b._slide(i,e,d);return f}).keyup(function(e){var d=a(this).data("index.ui-slider-handle");if(b._keySliding){b._stop(e,d);b._change(e,d);b._keySliding=false;a(this).removeClass("ui-state-active")}});this._refreshValue()},destroy:function(){this.handles.remove();this.range.remove();this.element.removeClass("ui-slider ui-slider-horizontal ui-slider-vertical ui-slider-disabled ui-widget ui-widget-content ui-corner-all").removeData("slider").unbind(".slider");this._mouseDestroy()},_mouseCapture:function(d){var e=this.options;if(e.disabled){return false}this.elementSize={width:this.element.outerWidth(),height:this.element.outerHeight()};this.elementOffset=this.element.offset();var h={x:d.pageX,y:d.pageY};var j=this._normValueFromMouse(h);var c=this._valueMax()-this._valueMin()+1,f;var k=this,i;this.handles.each(function(l){var m=Math.abs(j-k.values(l));if(c>m){c=m;f=a(this);i=l}});if(e.range==true&&this.values(1)==e.min){f=a(this.handles[++i])}this._start(d,i);k._handleIndex=i;f.addClass("ui-state-active").focus();var g=f.offset();var b=!a(d.target).parents().andSelf().is(".ui-slider-handle");this._clickOffset=b?{left:0,top:0}:{left:d.pageX-g.left-(f.width()/2),top:d.pageY-g.top-(f.height()/2)-(parseInt(f.css("borderTopWidth"),10)||0)-(parseInt(f.css("borderBottomWidth"),10)||0)+(parseInt(f.css("marginTop"),10)||0)};j=this._normValueFromMouse(h);this._slide(d,i,j);return true},_mouseStart:function(b){return true},_mouseDrag:function(d){var b={x:d.pageX,y:d.pageY};var c=this._normValueFromMouse(b);this._slide(d,this._handleIndex,c);return false},_mouseStop:function(b){this.handles.removeClass("ui-state-active");this._stop(b,this._handleIndex);this._change(b,this._handleIndex);this._handleIndex=null;this._clickOffset=null;return false},_detectOrientation:function(){this.orientation=this.options.orientation=="vertical"?"vertical":"horizontal"},_normValueFromMouse:function(d){var c,h;if("horizontal"==this.orientation){c=this.elementSize.width;h=d.x-this.elementOffset.left-(this._clickOffset?this._clickOffset.left:0)}else{c=this.elementSize.height;h=d.y-this.elementOffset.top-(this._clickOffset?this._clickOffset.top:0)}var f=(h/c);if(f>1){f=1}if(f<0){f=0}if("vertical"==this.orientation){f=1-f}var e=this._valueMax()-this._valueMin(),i=f*e,b=i%this.options.step,g=this._valueMin()+i-b;if(b>(this.options.step/2)){g+=this.options.step}return parseFloat(g.toFixed(5))},_start:function(d,c){var b={handle:this.handles[c],value:this.value()};if(this.options.values&&this.options.values.length){b.value=this.values(c);b.values=this.values()}this._trigger("start",d,b)},_slide:function(f,e,d){var g=this.handles[e];if(this.options.values&&this.options.values.length){var b=this.values(e?0:1);if((this.options.values.length==2&&this.options.range===true)&&((e==0&&d>b)||(e==1&&d<b))){d=b}if(d!=this.values(e)){var c=this.values();c[e]=d;var h=this._trigger("slide",f,{handle:this.handles[e],value:d,values:c});var b=this.values(e?0:1);if(h!==false){this.values(e,d,(f.type=="mousedown"&&this.options.animate),true)}}}else{if(d!=this.value()){var h=this._trigger("slide",f,{handle:this.handles[e],value:d});if(h!==false){this._setData("value",d,(f.type=="mousedown"&&this.options.animate))}}}},_stop:function(d,c){var b={handle:this.handles[c],value:this.value()};if(this.options.values&&this.options.values.length){b.value=this.values(c);b.values=this.values()}this._trigger("stop",d,b)},_change:function(d,c){var b={handle:this.handles[c],value:this.value()};if(this.options.values&&this.options.values.length){b.value=this.values(c);b.values=this.values()}this._trigger("change",d,b)},value:function(b){if(arguments.length){this._setData("value",b);this._change(null,0)}return this._value()},values:function(b,e,c,d){if(arguments.length>1){this.options.values[b]=e;this._refreshValue(c);if(!d){this._change(null,b)}}if(arguments.length){if(this.options.values&&this.options.values.length){return this._values(b)}else{return this.value()}}else{return this._values()}},_setData:function(b,d,c){a.widget.prototype._setData.apply(this,arguments);switch(b){case"disabled":if(d){this.handles.filter(".ui-state-focus").blur();this.handles.removeClass("ui-state-hover");this.handles.attr("disabled","disabled")}else{this.handles.removeAttr("disabled")}case"orientation":this._detectOrientation();this.element.removeClass("ui-slider-horizontal ui-slider-vertical").addClass("ui-slider-"+this.orientation);this._refreshValue(c);break;case"value":this._refreshValue(c);break}},_step:function(){var b=this.options.step;return b},_value:function(){var b=this.options.value;if(b<this._valueMin()){b=this._valueMin()}if(b>this._valueMax()){b=this._valueMax()}return b},_values:function(b){if(arguments.length){var c=this.options.values[b];if(c<this._valueMin()){c=this._valueMin()}if(c>this._valueMax()){c=this._valueMax()}return c}else{return this.options.values}},_valueMin:function(){var b=this.options.min;return b},_valueMax:function(){var b=this.options.max;return b},_refreshValue:function(c){var f=this.options.range,d=this.options,l=this;if(this.options.values&&this.options.values.length){var i,h;this.handles.each(function(p,n){var o=(l.values(p)-l._valueMin())/(l._valueMax()-l._valueMin())*100;var m={};m[l.orientation=="horizontal"?"left":"bottom"]=o+"%";a(this).stop(1,1)[c?"animate":"css"](m,d.animate);if(l.options.range===true){if(l.orientation=="horizontal"){(p==0)&&l.range.stop(1,1)[c?"animate":"css"]({left:o+"%"},d.animate);(p==1)&&l.range[c?"animate":"css"]({width:(o-lastValPercent)+"%"},{queue:false,duration:d.animate})}else{(p==0)&&l.range.stop(1,1)[c?"animate":"css"]({bottom:(o)+"%"},d.animate);(p==1)&&l.range[c?"animate":"css"]({height:(o-lastValPercent)+"%"},{queue:false,duration:d.animate})}}lastValPercent=o})}else{var j=this.value(),g=this._valueMin(),k=this._valueMax(),e=k!=g?(j-g)/(k-g)*100:0;var b={};b[l.orientation=="horizontal"?"left":"bottom"]=e+"%";this.handle.stop(1,1)[c?"animate":"css"](b,d.animate);(f=="min")&&(this.orientation=="horizontal")&&this.range.stop(1,1)[c?"animate":"css"]({width:e+"%"},d.animate);(f=="max")&&(this.orientation=="horizontal")&&this.range[c?"animate":"css"]({width:(100-e)+"%"},{queue:false,duration:d.animate});(f=="min")&&(this.orientation=="vertical")&&this.range.stop(1,1)[c?"animate":"css"]({height:e+"%"},d.animate);(f=="max")&&(this.orientation=="vertical")&&this.range[c?"animate":"css"]({height:(100-e)+"%"},{queue:false,duration:d.animate})}}}));a.extend(a.ui.slider,{getter:"value values",version:"1.7.2",eventPrefix:"slide",defaults:{animate:false,delay:0,distance:0,max:100,min:0,orientation:"horizontal",range:false,step:1,value:0,values:null}})})(jQuery);;/*
 * jQuery UI Tabs 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Tabs
 *
 * Depends:
 *	ui.core.js
 */
(function(a){a.widget("ui.tabs",{_init:function(){if(this.options.deselectable!==undefined){this.options.collapsible=this.options.deselectable}this._tabify(true)},_setData:function(b,c){if(b=="selected"){if(this.options.collapsible&&c==this.options.selected){return}this.select(c)}else{this.options[b]=c;if(b=="deselectable"){this.options.collapsible=c}this._tabify()}},_tabId:function(b){return b.title&&b.title.replace(/\s/g,"_").replace(/[^A-Za-z0-9\-_:\.]/g,"")||this.options.idPrefix+a.data(b)},_sanitizeSelector:function(b){return b.replace(/:/g,"\\:")},_cookie:function(){var b=this.cookie||(this.cookie=this.options.cookie.name||"ui-tabs-"+a.data(this.list[0]));return a.cookie.apply(null,[b].concat(a.makeArray(arguments)))},_ui:function(c,b){return{tab:c,panel:b,index:this.anchors.index(c)}},_cleanup:function(){this.lis.filter(".ui-state-processing").removeClass("ui-state-processing").find("span:data(label.tabs)").each(function(){var b=a(this);b.html(b.data("label.tabs")).removeData("label.tabs")})},_tabify:function(n){this.list=this.element.children("ul:first");this.lis=a("li:has(a[href])",this.list);this.anchors=this.lis.map(function(){return a("a",this)[0]});this.panels=a([]);var p=this,d=this.options;var c=/^#.+/;this.anchors.each(function(r,o){var q=a(o).attr("href");var s=q.split("#")[0],u;if(s&&(s===location.toString().split("#")[0]||(u=a("base")[0])&&s===u.href)){q=o.hash;o.href=q}if(c.test(q)){p.panels=p.panels.add(p._sanitizeSelector(q))}else{if(q!="#"){a.data(o,"href.tabs",q);a.data(o,"load.tabs",q.replace(/#.*$/,""));var w=p._tabId(o);o.href="#"+w;var v=a("#"+w);if(!v.length){v=a(d.panelTemplate).attr("id",w).addClass("ui-tabs-panel ui-widget-content ui-corner-bottom").insertAfter(p.panels[r-1]||p.list);v.data("destroy.tabs",true)}p.panels=p.panels.add(v)}else{d.disabled.push(r)}}});if(n){this.element.addClass("ui-tabs ui-widget ui-widget-content ui-corner-all");this.list.addClass("ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all");this.lis.addClass("ui-state-default ui-corner-top");this.panels.addClass("ui-tabs-panel ui-widget-content ui-corner-bottom");if(d.selected===undefined){if(location.hash){this.anchors.each(function(q,o){if(o.hash==location.hash){d.selected=q;return false}})}if(typeof d.selected!="number"&&d.cookie){d.selected=parseInt(p._cookie(),10)}if(typeof d.selected!="number"&&this.lis.filter(".ui-tabs-selected").length){d.selected=this.lis.index(this.lis.filter(".ui-tabs-selected"))}d.selected=d.selected||0}else{if(d.selected===null){d.selected=-1}}d.selected=((d.selected>=0&&this.anchors[d.selected])||d.selected<0)?d.selected:0;d.disabled=a.unique(d.disabled.concat(a.map(this.lis.filter(".ui-state-disabled"),function(q,o){return p.lis.index(q)}))).sort();if(a.inArray(d.selected,d.disabled)!=-1){d.disabled.splice(a.inArray(d.selected,d.disabled),1)}this.panels.addClass("ui-tabs-hide");this.lis.removeClass("ui-tabs-selected ui-state-active");if(d.selected>=0&&this.anchors.length){this.panels.eq(d.selected).removeClass("ui-tabs-hide");this.lis.eq(d.selected).addClass("ui-tabs-selected ui-state-active");p.element.queue("tabs",function(){p._trigger("show",null,p._ui(p.anchors[d.selected],p.panels[d.selected]))});this.load(d.selected)}a(window).bind("unload",function(){p.lis.add(p.anchors).unbind(".tabs");p.lis=p.anchors=p.panels=null})}else{d.selected=this.lis.index(this.lis.filter(".ui-tabs-selected"))}this.element[d.collapsible?"addClass":"removeClass"]("ui-tabs-collapsible");if(d.cookie){this._cookie(d.selected,d.cookie)}for(var g=0,m;(m=this.lis[g]);g++){a(m)[a.inArray(g,d.disabled)!=-1&&!a(m).hasClass("ui-tabs-selected")?"addClass":"removeClass"]("ui-state-disabled")}if(d.cache===false){this.anchors.removeData("cache.tabs")}this.lis.add(this.anchors).unbind(".tabs");if(d.event!="mouseover"){var f=function(o,i){if(i.is(":not(.ui-state-disabled)")){i.addClass("ui-state-"+o)}};var j=function(o,i){i.removeClass("ui-state-"+o)};this.lis.bind("mouseover.tabs",function(){f("hover",a(this))});this.lis.bind("mouseout.tabs",function(){j("hover",a(this))});this.anchors.bind("focus.tabs",function(){f("focus",a(this).closest("li"))});this.anchors.bind("blur.tabs",function(){j("focus",a(this).closest("li"))})}var b,h;if(d.fx){if(a.isArray(d.fx)){b=d.fx[0];h=d.fx[1]}else{b=h=d.fx}}function e(i,o){i.css({display:""});if(a.browser.msie&&o.opacity){i[0].style.removeAttribute("filter")}}var k=h?function(i,o){a(i).closest("li").removeClass("ui-state-default").addClass("ui-tabs-selected ui-state-active");o.hide().removeClass("ui-tabs-hide").animate(h,h.duration||"normal",function(){e(o,h);p._trigger("show",null,p._ui(i,o[0]))})}:function(i,o){a(i).closest("li").removeClass("ui-state-default").addClass("ui-tabs-selected ui-state-active");o.removeClass("ui-tabs-hide");p._trigger("show",null,p._ui(i,o[0]))};var l=b?function(o,i){i.animate(b,b.duration||"normal",function(){p.lis.removeClass("ui-tabs-selected ui-state-active").addClass("ui-state-default");i.addClass("ui-tabs-hide");e(i,b);p.element.dequeue("tabs")})}:function(o,i,q){p.lis.removeClass("ui-tabs-selected ui-state-active").addClass("ui-state-default");i.addClass("ui-tabs-hide");p.element.dequeue("tabs")};this.anchors.bind(d.event+".tabs",function(){var o=this,r=a(this).closest("li"),i=p.panels.filter(":not(.ui-tabs-hide)"),q=a(p._sanitizeSelector(this.hash));if((r.hasClass("ui-tabs-selected")&&!d.collapsible)||r.hasClass("ui-state-disabled")||r.hasClass("ui-state-processing")||p._trigger("select",null,p._ui(this,q[0]))===false){this.blur();return false}d.selected=p.anchors.index(this);p.abort();if(d.collapsible){if(r.hasClass("ui-tabs-selected")){d.selected=-1;if(d.cookie){p._cookie(d.selected,d.cookie)}p.element.queue("tabs",function(){l(o,i)}).dequeue("tabs");this.blur();return false}else{if(!i.length){if(d.cookie){p._cookie(d.selected,d.cookie)}p.element.queue("tabs",function(){k(o,q)});p.load(p.anchors.index(this));this.blur();return false}}}if(d.cookie){p._cookie(d.selected,d.cookie)}if(q.length){if(i.length){p.element.queue("tabs",function(){l(o,i)})}p.element.queue("tabs",function(){k(o,q)});p.load(p.anchors.index(this))}else{throw"jQuery UI Tabs: Mismatching fragment identifier."}if(a.browser.msie){this.blur()}});this.anchors.bind("click.tabs",function(){return false})},destroy:function(){var b=this.options;this.abort();this.element.unbind(".tabs").removeClass("ui-tabs ui-widget ui-widget-content ui-corner-all ui-tabs-collapsible").removeData("tabs");this.list.removeClass("ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all");this.anchors.each(function(){var c=a.data(this,"href.tabs");if(c){this.href=c}var d=a(this).unbind(".tabs");a.each(["href","load","cache"],function(e,f){d.removeData(f+".tabs")})});this.lis.unbind(".tabs").add(this.panels).each(function(){if(a.data(this,"destroy.tabs")){a(this).remove()}else{a(this).removeClass(["ui-state-default","ui-corner-top","ui-tabs-selected","ui-state-active","ui-state-hover","ui-state-focus","ui-state-disabled","ui-tabs-panel","ui-widget-content","ui-corner-bottom","ui-tabs-hide"].join(" "))}});if(b.cookie){this._cookie(null,b.cookie)}},add:function(e,d,c){if(c===undefined){c=this.anchors.length}var b=this,g=this.options,i=a(g.tabTemplate.replace(/#\{href\}/g,e).replace(/#\{label\}/g,d)),h=!e.indexOf("#")?e.replace("#",""):this._tabId(a("a",i)[0]);i.addClass("ui-state-default ui-corner-top").data("destroy.tabs",true);var f=a("#"+h);if(!f.length){f=a(g.panelTemplate).attr("id",h).data("destroy.tabs",true)}f.addClass("ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide");if(c>=this.lis.length){i.appendTo(this.list);f.appendTo(this.list[0].parentNode)}else{i.insertBefore(this.lis[c]);f.insertBefore(this.panels[c])}g.disabled=a.map(g.disabled,function(k,j){return k>=c?++k:k});this._tabify();if(this.anchors.length==1){i.addClass("ui-tabs-selected ui-state-active");f.removeClass("ui-tabs-hide");this.element.queue("tabs",function(){b._trigger("show",null,b._ui(b.anchors[0],b.panels[0]))});this.load(0)}this._trigger("add",null,this._ui(this.anchors[c],this.panels[c]))},remove:function(b){var d=this.options,e=this.lis.eq(b).remove(),c=this.panels.eq(b).remove();if(e.hasClass("ui-tabs-selected")&&this.anchors.length>1){this.select(b+(b+1<this.anchors.length?1:-1))}d.disabled=a.map(a.grep(d.disabled,function(g,f){return g!=b}),function(g,f){return g>=b?--g:g});this._tabify();this._trigger("remove",null,this._ui(e.find("a")[0],c[0]))},enable:function(b){var c=this.options;if(a.inArray(b,c.disabled)==-1){return}this.lis.eq(b).removeClass("ui-state-disabled");c.disabled=a.grep(c.disabled,function(e,d){return e!=b});this._trigger("enable",null,this._ui(this.anchors[b],this.panels[b]))},disable:function(c){var b=this,d=this.options;if(c!=d.selected){this.lis.eq(c).addClass("ui-state-disabled");d.disabled.push(c);d.disabled.sort();this._trigger("disable",null,this._ui(this.anchors[c],this.panels[c]))}},select:function(b){if(typeof b=="string"){b=this.anchors.index(this.anchors.filter("[href$="+b+"]"))}else{if(b===null){b=-1}}if(b==-1&&this.options.collapsible){b=this.options.selected}this.anchors.eq(b).trigger(this.options.event+".tabs")},load:function(e){var c=this,g=this.options,b=this.anchors.eq(e)[0],d=a.data(b,"load.tabs");this.abort();if(!d||this.element.queue("tabs").length!==0&&a.data(b,"cache.tabs")){this.element.dequeue("tabs");return}this.lis.eq(e).addClass("ui-state-processing");if(g.spinner){var f=a("span",b);f.data("label.tabs",f.html()).html(g.spinner)}this.xhr=a.ajax(a.extend({},g.ajaxOptions,{url:d,success:function(i,h){a(c._sanitizeSelector(b.hash)).html(i);c._cleanup();if(g.cache){a.data(b,"cache.tabs",true)}c._trigger("load",null,c._ui(c.anchors[e],c.panels[e]));try{g.ajaxOptions.success(i,h)}catch(j){}c.element.dequeue("tabs")}}))},abort:function(){this.element.queue([]);this.panels.stop(false,true);if(this.xhr){this.xhr.abort();delete this.xhr}this._cleanup()},url:function(c,b){this.anchors.eq(c).removeData("cache.tabs").data("load.tabs",b)},length:function(){return this.anchors.length}});a.extend(a.ui.tabs,{version:"1.7.2",getter:"length",defaults:{ajaxOptions:null,cache:false,cookie:null,collapsible:false,disabled:[],event:"click",fx:null,idPrefix:"ui-tabs-",panelTemplate:"<div></div>",spinner:"<em>Loading&#8230;</em>",tabTemplate:'<li><a href="#{href}"><span>#{label}</span></a></li>'}});a.extend(a.ui.tabs.prototype,{rotation:null,rotate:function(d,f){var b=this,g=this.options;var c=b._rotate||(b._rotate=function(h){clearTimeout(b.rotation);b.rotation=setTimeout(function(){var i=g.selected;b.select(++i<b.anchors.length?i:0)},d);if(h){h.stopPropagation()}});var e=b._unrotate||(b._unrotate=!f?function(h){if(h.clientX){b.rotate(null)}}:function(h){t=g.selected;c()});if(d){this.element.bind("tabsshow",c);this.anchors.bind(g.event+".tabs",e);c()}else{clearTimeout(b.rotation);this.element.unbind("tabsshow",c);this.anchors.unbind(g.event+".tabs",e);delete this._rotate;delete this._unrotate}}})})(jQuery);;/*
 * jQuery UI Datepicker 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Datepicker
 *
 * Depends:
 *	ui.core.js
 */
(function($){$.extend($.ui,{datepicker:{version:"1.7.2"}});var PROP_NAME="datepicker";function Datepicker(){this.debug=false;this._curInst=null;this._keyEvent=false;this._disabledInputs=[];this._datepickerShowing=false;this._inDialog=false;this._mainDivId="ui-datepicker-div";this._inlineClass="ui-datepicker-inline";this._appendClass="ui-datepicker-append";this._triggerClass="ui-datepicker-trigger";this._dialogClass="ui-datepicker-dialog";this._disableClass="ui-datepicker-disabled";this._unselectableClass="ui-datepicker-unselectable";this._currentClass="ui-datepicker-current-day";this._dayOverClass="ui-datepicker-days-cell-over";this.regional=[];this.regional[""]={closeText:"Done",prevText:"Prev",nextText:"Next",currentText:"Today",monthNames:["January","February","March","April","May","June","July","August","September","October","November","December"],monthNamesShort:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],dayNames:["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],dayNamesShort:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],dayNamesMin:["Su","Mo","Tu","We","Th","Fr","Sa"],dateFormat:"mm/dd/yy",firstDay:0,isRTL:false};this._defaults={showOn:"focus",showAnim:"show",showOptions:{},defaultDate:null,appendText:"",buttonText:"...",buttonImage:"",buttonImageOnly:false,hideIfNoPrevNext:false,navigationAsDateFormat:false,gotoCurrent:false,changeMonth:false,changeYear:false,showMonthAfterYear:false,yearRange:"-10:+10",showOtherMonths:false,calculateWeek:this.iso8601Week,shortYearCutoff:"+10",minDate:null,maxDate:null,duration:"normal",beforeShowDay:null,beforeShow:null,onSelect:null,onChangeMonthYear:null,onClose:null,numberOfMonths:1,showCurrentAtPos:0,stepMonths:1,stepBigMonths:12,altField:"",altFormat:"",constrainInput:true,showButtonPanel:false};$.extend(this._defaults,this.regional[""]);this.dpDiv=$('<div id="'+this._mainDivId+'" class="ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all ui-helper-hidden-accessible"></div>')}$.extend(Datepicker.prototype,{markerClassName:"hasDatepicker",log:function(){if(this.debug){console.log.apply("",arguments)}},setDefaults:function(settings){extendRemove(this._defaults,settings||{});return this},_attachDatepicker:function(target,settings){var inlineSettings=null;for(var attrName in this._defaults){var attrValue=target.getAttribute("date:"+attrName);if(attrValue){inlineSettings=inlineSettings||{};try{inlineSettings[attrName]=eval(attrValue)}catch(err){inlineSettings[attrName]=attrValue}}}var nodeName=target.nodeName.toLowerCase();var inline=(nodeName=="div"||nodeName=="span");if(!target.id){target.id="dp"+(++this.uuid)}var inst=this._newInst($(target),inline);inst.settings=$.extend({},settings||{},inlineSettings||{});if(nodeName=="input"){this._connectDatepicker(target,inst)}else{if(inline){this._inlineDatepicker(target,inst)}}},_newInst:function(target,inline){var id=target[0].id.replace(/([:\[\]\.])/g,"\\\\$1");return{id:id,input:target,selectedDay:0,selectedMonth:0,selectedYear:0,drawMonth:0,drawYear:0,inline:inline,dpDiv:(!inline?this.dpDiv:$('<div class="'+this._inlineClass+' ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all"></div>'))}},_connectDatepicker:function(target,inst){var input=$(target);inst.append=$([]);inst.trigger=$([]);if(input.hasClass(this.markerClassName)){return}var appendText=this._get(inst,"appendText");var isRTL=this._get(inst,"isRTL");if(appendText){inst.append=$('<span class="'+this._appendClass+'">'+appendText+"</span>");input[isRTL?"before":"after"](inst.append)}var showOn=this._get(inst,"showOn");if(showOn=="focus"||showOn=="both"){input.focus(this._showDatepicker)}if(showOn=="button"||showOn=="both"){var buttonText=this._get(inst,"buttonText");var buttonImage=this._get(inst,"buttonImage");inst.trigger=$(this._get(inst,"buttonImageOnly")?$("<img/>").addClass(this._triggerClass).attr({src:buttonImage,alt:buttonText,title:buttonText}):$('<button type="button"></button>').addClass(this._triggerClass).html(buttonImage==""?buttonText:$("<img/>").attr({src:buttonImage,alt:buttonText,title:buttonText})));input[isRTL?"before":"after"](inst.trigger);inst.trigger.click(function(){if($.datepicker._datepickerShowing&&$.datepicker._lastInput==target){$.datepicker._hideDatepicker()}else{$.datepicker._showDatepicker(target)}return false})}input.addClass(this.markerClassName).keydown(this._doKeyDown).keypress(this._doKeyPress).bind("setData.datepicker",function(event,key,value){inst.settings[key]=value}).bind("getData.datepicker",function(event,key){return this._get(inst,key)});$.data(target,PROP_NAME,inst)},_inlineDatepicker:function(target,inst){var divSpan=$(target);if(divSpan.hasClass(this.markerClassName)){return}divSpan.addClass(this.markerClassName).append(inst.dpDiv).bind("setData.datepicker",function(event,key,value){inst.settings[key]=value}).bind("getData.datepicker",function(event,key){return this._get(inst,key)});$.data(target,PROP_NAME,inst);this._setDate(inst,this._getDefaultDate(inst));this._updateDatepicker(inst);this._updateAlternate(inst)},_dialogDatepicker:function(input,dateText,onSelect,settings,pos){var inst=this._dialogInst;if(!inst){var id="dp"+(++this.uuid);this._dialogInput=$('<input type="text" id="'+id+'" size="1" style="position: absolute; top: -100px;"/>');this._dialogInput.keydown(this._doKeyDown);$("body").append(this._dialogInput);inst=this._dialogInst=this._newInst(this._dialogInput,false);inst.settings={};$.data(this._dialogInput[0],PROP_NAME,inst)}extendRemove(inst.settings,settings||{});this._dialogInput.val(dateText);this._pos=(pos?(pos.length?pos:[pos.pageX,pos.pageY]):null);if(!this._pos){var browserWidth=window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth;var browserHeight=window.innerHeight||document.documentElement.clientHeight||document.body.clientHeight;var scrollX=document.documentElement.scrollLeft||document.body.scrollLeft;var scrollY=document.documentElement.scrollTop||document.body.scrollTop;this._pos=[(browserWidth/2)-100+scrollX,(browserHeight/2)-150+scrollY]}this._dialogInput.css("left",this._pos[0]+"px").css("top",this._pos[1]+"px");inst.settings.onSelect=onSelect;this._inDialog=true;this.dpDiv.addClass(this._dialogClass);this._showDatepicker(this._dialogInput[0]);if($.blockUI){$.blockUI(this.dpDiv)}$.data(this._dialogInput[0],PROP_NAME,inst);return this},_destroyDatepicker:function(target){var $target=$(target);var inst=$.data(target,PROP_NAME);if(!$target.hasClass(this.markerClassName)){return}var nodeName=target.nodeName.toLowerCase();$.removeData(target,PROP_NAME);if(nodeName=="input"){inst.append.remove();inst.trigger.remove();$target.removeClass(this.markerClassName).unbind("focus",this._showDatepicker).unbind("keydown",this._doKeyDown).unbind("keypress",this._doKeyPress)}else{if(nodeName=="div"||nodeName=="span"){$target.removeClass(this.markerClassName).empty()}}},_enableDatepicker:function(target){var $target=$(target);var inst=$.data(target,PROP_NAME);if(!$target.hasClass(this.markerClassName)){return}var nodeName=target.nodeName.toLowerCase();if(nodeName=="input"){target.disabled=false;inst.trigger.filter("button").each(function(){this.disabled=false}).end().filter("img").css({opacity:"1.0",cursor:""})}else{if(nodeName=="div"||nodeName=="span"){var inline=$target.children("."+this._inlineClass);inline.children().removeClass("ui-state-disabled")}}this._disabledInputs=$.map(this._disabledInputs,function(value){return(value==target?null:value)})},_disableDatepicker:function(target){var $target=$(target);var inst=$.data(target,PROP_NAME);if(!$target.hasClass(this.markerClassName)){return}var nodeName=target.nodeName.toLowerCase();if(nodeName=="input"){target.disabled=true;inst.trigger.filter("button").each(function(){this.disabled=true}).end().filter("img").css({opacity:"0.5",cursor:"default"})}else{if(nodeName=="div"||nodeName=="span"){var inline=$target.children("."+this._inlineClass);inline.children().addClass("ui-state-disabled")}}this._disabledInputs=$.map(this._disabledInputs,function(value){return(value==target?null:value)});this._disabledInputs[this._disabledInputs.length]=target},_isDisabledDatepicker:function(target){if(!target){return false}for(var i=0;i<this._disabledInputs.length;i++){if(this._disabledInputs[i]==target){return true}}return false},_getInst:function(target){try{return $.data(target,PROP_NAME)}catch(err){throw"Missing instance data for this datepicker"}},_optionDatepicker:function(target,name,value){var inst=this._getInst(target);if(arguments.length==2&&typeof name=="string"){return(name=="defaults"?$.extend({},$.datepicker._defaults):(inst?(name=="all"?$.extend({},inst.settings):this._get(inst,name)):null))}var settings=name||{};if(typeof name=="string"){settings={};settings[name]=value}if(inst){if(this._curInst==inst){this._hideDatepicker(null)}var date=this._getDateDatepicker(target);extendRemove(inst.settings,settings);this._setDateDatepicker(target,date);this._updateDatepicker(inst)}},_changeDatepicker:function(target,name,value){this._optionDatepicker(target,name,value)},_refreshDatepicker:function(target){var inst=this._getInst(target);if(inst){this._updateDatepicker(inst)}},_setDateDatepicker:function(target,date,endDate){var inst=this._getInst(target);if(inst){this._setDate(inst,date,endDate);this._updateDatepicker(inst);this._updateAlternate(inst)}},_getDateDatepicker:function(target){var inst=this._getInst(target);if(inst&&!inst.inline){this._setDateFromField(inst)}return(inst?this._getDate(inst):null)},_doKeyDown:function(event){var inst=$.datepicker._getInst(event.target);var handled=true;var isRTL=inst.dpDiv.is(".ui-datepicker-rtl");inst._keyEvent=true;if($.datepicker._datepickerShowing){switch(event.keyCode){case 9:$.datepicker._hideDatepicker(null,"");break;case 13:var sel=$("td."+$.datepicker._dayOverClass+", td."+$.datepicker._currentClass,inst.dpDiv);if(sel[0]){$.datepicker._selectDay(event.target,inst.selectedMonth,inst.selectedYear,sel[0])}else{$.datepicker._hideDatepicker(null,$.datepicker._get(inst,"duration"))}return false;break;case 27:$.datepicker._hideDatepicker(null,$.datepicker._get(inst,"duration"));break;case 33:$.datepicker._adjustDate(event.target,(event.ctrlKey?-$.datepicker._get(inst,"stepBigMonths"):-$.datepicker._get(inst,"stepMonths")),"M");break;case 34:$.datepicker._adjustDate(event.target,(event.ctrlKey?+$.datepicker._get(inst,"stepBigMonths"):+$.datepicker._get(inst,"stepMonths")),"M");break;case 35:if(event.ctrlKey||event.metaKey){$.datepicker._clearDate(event.target)}handled=event.ctrlKey||event.metaKey;break;case 36:if(event.ctrlKey||event.metaKey){$.datepicker._gotoToday(event.target)}handled=event.ctrlKey||event.metaKey;break;case 37:if(event.ctrlKey||event.metaKey){$.datepicker._adjustDate(event.target,(isRTL?+1:-1),"D")}handled=event.ctrlKey||event.metaKey;if(event.originalEvent.altKey){$.datepicker._adjustDate(event.target,(event.ctrlKey?-$.datepicker._get(inst,"stepBigMonths"):-$.datepicker._get(inst,"stepMonths")),"M")}break;case 38:if(event.ctrlKey||event.metaKey){$.datepicker._adjustDate(event.target,-7,"D")}handled=event.ctrlKey||event.metaKey;break;case 39:if(event.ctrlKey||event.metaKey){$.datepicker._adjustDate(event.target,(isRTL?-1:+1),"D")}handled=event.ctrlKey||event.metaKey;if(event.originalEvent.altKey){$.datepicker._adjustDate(event.target,(event.ctrlKey?+$.datepicker._get(inst,"stepBigMonths"):+$.datepicker._get(inst,"stepMonths")),"M")}break;case 40:if(event.ctrlKey||event.metaKey){$.datepicker._adjustDate(event.target,+7,"D")}handled=event.ctrlKey||event.metaKey;break;default:handled=false}}else{if(event.keyCode==36&&event.ctrlKey){$.datepicker._showDatepicker(this)}else{handled=false}}if(handled){event.preventDefault();event.stopPropagation()}},_doKeyPress:function(event){var inst=$.datepicker._getInst(event.target);if($.datepicker._get(inst,"constrainInput")){var chars=$.datepicker._possibleChars($.datepicker._get(inst,"dateFormat"));var chr=String.fromCharCode(event.charCode==undefined?event.keyCode:event.charCode);return event.ctrlKey||(chr<" "||!chars||chars.indexOf(chr)>-1)}},_showDatepicker:function(input){input=input.target||input;if(input.nodeName.toLowerCase()!="input"){input=$("input",input.parentNode)[0]}if($.datepicker._isDisabledDatepicker(input)||$.datepicker._lastInput==input){return}var inst=$.datepicker._getInst(input);var beforeShow=$.datepicker._get(inst,"beforeShow");extendRemove(inst.settings,(beforeShow?beforeShow.apply(input,[input,inst]):{}));$.datepicker._hideDatepicker(null,"");$.datepicker._lastInput=input;$.datepicker._setDateFromField(inst);if($.datepicker._inDialog){input.value=""}if(!$.datepicker._pos){$.datepicker._pos=$.datepicker._findPos(input);$.datepicker._pos[1]+=input.offsetHeight}var isFixed=false;$(input).parents().each(function(){isFixed|=$(this).css("position")=="fixed";return !isFixed});if(isFixed&&$.browser.opera){$.datepicker._pos[0]-=document.documentElement.scrollLeft;$.datepicker._pos[1]-=document.documentElement.scrollTop}var offset={left:$.datepicker._pos[0],top:$.datepicker._pos[1]};$.datepicker._pos=null;inst.rangeStart=null;inst.dpDiv.css({position:"absolute",display:"block",top:"-1000px"});$.datepicker._updateDatepicker(inst);offset=$.datepicker._checkOffset(inst,offset,isFixed);inst.dpDiv.css({position:($.datepicker._inDialog&&$.blockUI?"static":(isFixed?"fixed":"absolute")),display:"none",left:offset.left+"px",top:offset.top+"px"});if(!inst.inline){var showAnim=$.datepicker._get(inst,"showAnim")||"show";var duration=$.datepicker._get(inst,"duration");var postProcess=function(){$.datepicker._datepickerShowing=true;if($.browser.msie&&parseInt($.browser.version,10)<7){$("iframe.ui-datepicker-cover").css({width:inst.dpDiv.width()+4,height:inst.dpDiv.height()+4})}};if($.effects&&$.effects[showAnim]){inst.dpDiv.show(showAnim,$.datepicker._get(inst,"showOptions"),duration,postProcess)}else{inst.dpDiv[showAnim](duration,postProcess)}if(duration==""){postProcess()}if(inst.input[0].type!="hidden"){inst.input[0].focus()}$.datepicker._curInst=inst}},_updateDatepicker:function(inst){var dims={width:inst.dpDiv.width()+4,height:inst.dpDiv.height()+4};var self=this;inst.dpDiv.empty().append(this._generateHTML(inst)).find("iframe.ui-datepicker-cover").css({width:dims.width,height:dims.height}).end().find("button, .ui-datepicker-prev, .ui-datepicker-next, .ui-datepicker-calendar td a").bind("mouseout",function(){$(this).removeClass("ui-state-hover");if(this.className.indexOf("ui-datepicker-prev")!=-1){$(this).removeClass("ui-datepicker-prev-hover")}if(this.className.indexOf("ui-datepicker-next")!=-1){$(this).removeClass("ui-datepicker-next-hover")}}).bind("mouseover",function(){if(!self._isDisabledDatepicker(inst.inline?inst.dpDiv.parent()[0]:inst.input[0])){$(this).parents(".ui-datepicker-calendar").find("a").removeClass("ui-state-hover");$(this).addClass("ui-state-hover");if(this.className.indexOf("ui-datepicker-prev")!=-1){$(this).addClass("ui-datepicker-prev-hover")}if(this.className.indexOf("ui-datepicker-next")!=-1){$(this).addClass("ui-datepicker-next-hover")}}}).end().find("."+this._dayOverClass+" a").trigger("mouseover").end();var numMonths=this._getNumberOfMonths(inst);var cols=numMonths[1];var width=17;if(cols>1){inst.dpDiv.addClass("ui-datepicker-multi-"+cols).css("width",(width*cols)+"em")}else{inst.dpDiv.removeClass("ui-datepicker-multi-2 ui-datepicker-multi-3 ui-datepicker-multi-4").width("")}inst.dpDiv[(numMonths[0]!=1||numMonths[1]!=1?"add":"remove")+"Class"]("ui-datepicker-multi");inst.dpDiv[(this._get(inst,"isRTL")?"add":"remove")+"Class"]("ui-datepicker-rtl");if(inst.input&&inst.input[0].type!="hidden"&&inst==$.datepicker._curInst){$(inst.input[0]).focus()}},_checkOffset:function(inst,offset,isFixed){var dpWidth=inst.dpDiv.outerWidth();var dpHeight=inst.dpDiv.outerHeight();var inputWidth=inst.input?inst.input.outerWidth():0;var inputHeight=inst.input?inst.input.outerHeight():0;var viewWidth=(window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth)+$(document).scrollLeft();var viewHeight=(window.innerHeight||document.documentElement.clientHeight||document.body.clientHeight)+$(document).scrollTop();offset.left-=(this._get(inst,"isRTL")?(dpWidth-inputWidth):0);offset.left-=(isFixed&&offset.left==inst.input.offset().left)?$(document).scrollLeft():0;offset.top-=(isFixed&&offset.top==(inst.input.offset().top+inputHeight))?$(document).scrollTop():0;offset.left-=(offset.left+dpWidth>viewWidth&&viewWidth>dpWidth)?Math.abs(offset.left+dpWidth-viewWidth):0;offset.top-=(offset.top+dpHeight>viewHeight&&viewHeight>dpHeight)?Math.abs(offset.top+dpHeight+inputHeight*2-viewHeight):0;return offset},_findPos:function(obj){while(obj&&(obj.type=="hidden"||obj.nodeType!=1)){obj=obj.nextSibling}var position=$(obj).offset();return[position.left,position.top]},_hideDatepicker:function(input,duration){var inst=this._curInst;if(!inst||(input&&inst!=$.data(input,PROP_NAME))){return}if(inst.stayOpen){this._selectDate("#"+inst.id,this._formatDate(inst,inst.currentDay,inst.currentMonth,inst.currentYear))}inst.stayOpen=false;if(this._datepickerShowing){duration=(duration!=null?duration:this._get(inst,"duration"));var showAnim=this._get(inst,"showAnim");var postProcess=function(){$.datepicker._tidyDialog(inst)};if(duration!=""&&$.effects&&$.effects[showAnim]){inst.dpDiv.hide(showAnim,$.datepicker._get(inst,"showOptions"),duration,postProcess)}else{inst.dpDiv[(duration==""?"hide":(showAnim=="slideDown"?"slideUp":(showAnim=="fadeIn"?"fadeOut":"hide")))](duration,postProcess)}if(duration==""){this._tidyDialog(inst)}var onClose=this._get(inst,"onClose");if(onClose){onClose.apply((inst.input?inst.input[0]:null),[(inst.input?inst.input.val():""),inst])}this._datepickerShowing=false;this._lastInput=null;if(this._inDialog){this._dialogInput.css({position:"absolute",left:"0",top:"-100px"});if($.blockUI){$.unblockUI();$("body").append(this.dpDiv)}}this._inDialog=false}this._curInst=null},_tidyDialog:function(inst){inst.dpDiv.removeClass(this._dialogClass).unbind(".ui-datepicker-calendar")},_checkExternalClick:function(event){if(!$.datepicker._curInst){return}var $target=$(event.target);if(($target.parents("#"+$.datepicker._mainDivId).length==0)&&!$target.hasClass($.datepicker.markerClassName)&&!$target.hasClass($.datepicker._triggerClass)&&$.datepicker._datepickerShowing&&!($.datepicker._inDialog&&$.blockUI)){$.datepicker._hideDatepicker(null,"")}},_adjustDate:function(id,offset,period){var target=$(id);var inst=this._getInst(target[0]);if(this._isDisabledDatepicker(target[0])){return}this._adjustInstDate(inst,offset+(period=="M"?this._get(inst,"showCurrentAtPos"):0),period);this._updateDatepicker(inst)},_gotoToday:function(id){var target=$(id);var inst=this._getInst(target[0]);if(this._get(inst,"gotoCurrent")&&inst.currentDay){inst.selectedDay=inst.currentDay;inst.drawMonth=inst.selectedMonth=inst.currentMonth;inst.drawYear=inst.selectedYear=inst.currentYear}else{var date=new Date();inst.selectedDay=date.getDate();inst.drawMonth=inst.selectedMonth=date.getMonth();inst.drawYear=inst.selectedYear=date.getFullYear()}this._notifyChange(inst);this._adjustDate(target)},_selectMonthYear:function(id,select,period){var target=$(id);var inst=this._getInst(target[0]);inst._selectingMonthYear=false;inst["selected"+(period=="M"?"Month":"Year")]=inst["draw"+(period=="M"?"Month":"Year")]=parseInt(select.options[select.selectedIndex].value,10);this._notifyChange(inst);this._adjustDate(target)},_clickMonthYear:function(id){var target=$(id);var inst=this._getInst(target[0]);if(inst.input&&inst._selectingMonthYear&&!$.browser.msie){inst.input[0].focus()}inst._selectingMonthYear=!inst._selectingMonthYear},_selectDay:function(id,month,year,td){var target=$(id);if($(td).hasClass(this._unselectableClass)||this._isDisabledDatepicker(target[0])){return}var inst=this._getInst(target[0]);inst.selectedDay=inst.currentDay=$("a",td).html();inst.selectedMonth=inst.currentMonth=month;inst.selectedYear=inst.currentYear=year;if(inst.stayOpen){inst.endDay=inst.endMonth=inst.endYear=null}this._selectDate(id,this._formatDate(inst,inst.currentDay,inst.currentMonth,inst.currentYear));if(inst.stayOpen){inst.rangeStart=this._daylightSavingAdjust(new Date(inst.currentYear,inst.currentMonth,inst.currentDay));this._updateDatepicker(inst)}},_clearDate:function(id){var target=$(id);var inst=this._getInst(target[0]);inst.stayOpen=false;inst.endDay=inst.endMonth=inst.endYear=inst.rangeStart=null;this._selectDate(target,"")},_selectDate:function(id,dateStr){var target=$(id);var inst=this._getInst(target[0]);dateStr=(dateStr!=null?dateStr:this._formatDate(inst));if(inst.input){inst.input.val(dateStr)}this._updateAlternate(inst);var onSelect=this._get(inst,"onSelect");if(onSelect){onSelect.apply((inst.input?inst.input[0]:null),[dateStr,inst])}else{if(inst.input){inst.input.trigger("change")}}if(inst.inline){this._updateDatepicker(inst)}else{if(!inst.stayOpen){this._hideDatepicker(null,this._get(inst,"duration"));this._lastInput=inst.input[0];if(typeof(inst.input[0])!="object"){inst.input[0].focus()}this._lastInput=null}}},_updateAlternate:function(inst){var altField=this._get(inst,"altField");if(altField){var altFormat=this._get(inst,"altFormat")||this._get(inst,"dateFormat");var date=this._getDate(inst);dateStr=this.formatDate(altFormat,date,this._getFormatConfig(inst));$(altField).each(function(){$(this).val(dateStr)})}},noWeekends:function(date){var day=date.getDay();return[(day>0&&day<6),""]},iso8601Week:function(date){var checkDate=new Date(date.getFullYear(),date.getMonth(),date.getDate());var firstMon=new Date(checkDate.getFullYear(),1-1,4);var firstDay=firstMon.getDay()||7;firstMon.setDate(firstMon.getDate()+1-firstDay);if(firstDay<4&&checkDate<firstMon){checkDate.setDate(checkDate.getDate()-3);return $.datepicker.iso8601Week(checkDate)}else{if(checkDate>new Date(checkDate.getFullYear(),12-1,28)){firstDay=new Date(checkDate.getFullYear()+1,1-1,4).getDay()||7;if(firstDay>4&&(checkDate.getDay()||7)<firstDay-3){return 1}}}return Math.floor(((checkDate-firstMon)/86400000)/7)+1},parseDate:function(format,value,settings){if(format==null||value==null){throw"Invalid arguments"}value=(typeof value=="object"?value.toString():value+"");if(value==""){return null}var shortYearCutoff=(settings?settings.shortYearCutoff:null)||this._defaults.shortYearCutoff;var dayNamesShort=(settings?settings.dayNamesShort:null)||this._defaults.dayNamesShort;var dayNames=(settings?settings.dayNames:null)||this._defaults.dayNames;var monthNamesShort=(settings?settings.monthNamesShort:null)||this._defaults.monthNamesShort;var monthNames=(settings?settings.monthNames:null)||this._defaults.monthNames;var year=-1;var month=-1;var day=-1;var doy=-1;var literal=false;var lookAhead=function(match){var matches=(iFormat+1<format.length&&format.charAt(iFormat+1)==match);if(matches){iFormat++}return matches};var getNumber=function(match){lookAhead(match);var origSize=(match=="@"?14:(match=="y"?4:(match=="o"?3:2)));var size=origSize;var num=0;while(size>0&&iValue<value.length&&value.charAt(iValue)>="0"&&value.charAt(iValue)<="9"){num=num*10+parseInt(value.charAt(iValue++),10);size--}if(size==origSize){throw"Missing number at position "+iValue}return num};var getName=function(match,shortNames,longNames){var names=(lookAhead(match)?longNames:shortNames);var size=0;for(var j=0;j<names.length;j++){size=Math.max(size,names[j].length)}var name="";var iInit=iValue;while(size>0&&iValue<value.length){name+=value.charAt(iValue++);for(var i=0;i<names.length;i++){if(name==names[i]){return i+1}}size--}throw"Unknown name at position "+iInit};var checkLiteral=function(){if(value.charAt(iValue)!=format.charAt(iFormat)){throw"Unexpected literal at position "+iValue}iValue++};var iValue=0;for(var iFormat=0;iFormat<format.length;iFormat++){if(literal){if(format.charAt(iFormat)=="'"&&!lookAhead("'")){literal=false}else{checkLiteral()}}else{switch(format.charAt(iFormat)){case"d":day=getNumber("d");break;case"D":getName("D",dayNamesShort,dayNames);break;case"o":doy=getNumber("o");break;case"m":month=getNumber("m");break;case"M":month=getName("M",monthNamesShort,monthNames);break;case"y":year=getNumber("y");break;case"@":var date=new Date(getNumber("@"));year=date.getFullYear();month=date.getMonth()+1;day=date.getDate();break;case"'":if(lookAhead("'")){checkLiteral()}else{literal=true}break;default:checkLiteral()}}}if(year==-1){year=new Date().getFullYear()}else{if(year<100){year+=new Date().getFullYear()-new Date().getFullYear()%100+(year<=shortYearCutoff?0:-100)}}if(doy>-1){month=1;day=doy;do{var dim=this._getDaysInMonth(year,month-1);if(day<=dim){break}month++;day-=dim}while(true)}var date=this._daylightSavingAdjust(new Date(year,month-1,day));if(date.getFullYear()!=year||date.getMonth()+1!=month||date.getDate()!=day){throw"Invalid date"}return date},ATOM:"yy-mm-dd",COOKIE:"D, dd M yy",ISO_8601:"yy-mm-dd",RFC_822:"D, d M y",RFC_850:"DD, dd-M-y",RFC_1036:"D, d M y",RFC_1123:"D, d M yy",RFC_2822:"D, d M yy",RSS:"D, d M y",TIMESTAMP:"@",W3C:"yy-mm-dd",formatDate:function(format,date,settings){if(!date){return""}var dayNamesShort=(settings?settings.dayNamesShort:null)||this._defaults.dayNamesShort;var dayNames=(settings?settings.dayNames:null)||this._defaults.dayNames;var monthNamesShort=(settings?settings.monthNamesShort:null)||this._defaults.monthNamesShort;var monthNames=(settings?settings.monthNames:null)||this._defaults.monthNames;var lookAhead=function(match){var matches=(iFormat+1<format.length&&format.charAt(iFormat+1)==match);if(matches){iFormat++}return matches};var formatNumber=function(match,value,len){var num=""+value;if(lookAhead(match)){while(num.length<len){num="0"+num}}return num};var formatName=function(match,value,shortNames,longNames){return(lookAhead(match)?longNames[value]:shortNames[value])};var output="";var literal=false;if(date){for(var iFormat=0;iFormat<format.length;iFormat++){if(literal){if(format.charAt(iFormat)=="'"&&!lookAhead("'")){literal=false}else{output+=format.charAt(iFormat)}}else{switch(format.charAt(iFormat)){case"d":output+=formatNumber("d",date.getDate(),2);break;case"D":output+=formatName("D",date.getDay(),dayNamesShort,dayNames);break;case"o":var doy=date.getDate();for(var m=date.getMonth()-1;m>=0;m--){doy+=this._getDaysInMonth(date.getFullYear(),m)}output+=formatNumber("o",doy,3);break;case"m":output+=formatNumber("m",date.getMonth()+1,2);break;case"M":output+=formatName("M",date.getMonth(),monthNamesShort,monthNames);break;case"y":output+=(lookAhead("y")?date.getFullYear():(date.getYear()%100<10?"0":"")+date.getYear()%100);break;case"@":output+=date.getTime();break;case"'":if(lookAhead("'")){output+="'"}else{literal=true}break;default:output+=format.charAt(iFormat)}}}}return output},_possibleChars:function(format){var chars="";var literal=false;for(var iFormat=0;iFormat<format.length;iFormat++){if(literal){if(format.charAt(iFormat)=="'"&&!lookAhead("'")){literal=false}else{chars+=format.charAt(iFormat)}}else{switch(format.charAt(iFormat)){case"d":case"m":case"y":case"@":chars+="0123456789";break;case"D":case"M":return null;case"'":if(lookAhead("'")){chars+="'"}else{literal=true}break;default:chars+=format.charAt(iFormat)}}}return chars},_get:function(inst,name){return inst.settings[name]!==undefined?inst.settings[name]:this._defaults[name]},_setDateFromField:function(inst){var dateFormat=this._get(inst,"dateFormat");var dates=inst.input?inst.input.val():null;inst.endDay=inst.endMonth=inst.endYear=null;var date=defaultDate=this._getDefaultDate(inst);var settings=this._getFormatConfig(inst);try{date=this.parseDate(dateFormat,dates,settings)||defaultDate}catch(event){this.log(event);date=defaultDate}inst.selectedDay=date.getDate();inst.drawMonth=inst.selectedMonth=date.getMonth();inst.drawYear=inst.selectedYear=date.getFullYear();inst.currentDay=(dates?date.getDate():0);inst.currentMonth=(dates?date.getMonth():0);inst.currentYear=(dates?date.getFullYear():0);this._adjustInstDate(inst)},_getDefaultDate:function(inst){var date=this._determineDate(this._get(inst,"defaultDate"),new Date());var minDate=this._getMinMaxDate(inst,"min",true);var maxDate=this._getMinMaxDate(inst,"max");date=(minDate&&date<minDate?minDate:date);date=(maxDate&&date>maxDate?maxDate:date);return date},_determineDate:function(date,defaultDate){var offsetNumeric=function(offset){var date=new Date();date.setDate(date.getDate()+offset);return date};var offsetString=function(offset,getDaysInMonth){var date=new Date();var year=date.getFullYear();var month=date.getMonth();var day=date.getDate();var pattern=/([+-]?[0-9]+)\s*(d|D|w|W|m|M|y|Y)?/g;var matches=pattern.exec(offset);while(matches){switch(matches[2]||"d"){case"d":case"D":day+=parseInt(matches[1],10);break;case"w":case"W":day+=parseInt(matches[1],10)*7;break;case"m":case"M":month+=parseInt(matches[1],10);day=Math.min(day,getDaysInMonth(year,month));break;case"y":case"Y":year+=parseInt(matches[1],10);day=Math.min(day,getDaysInMonth(year,month));break}matches=pattern.exec(offset)}return new Date(year,month,day)};date=(date==null?defaultDate:(typeof date=="string"?offsetString(date,this._getDaysInMonth):(typeof date=="number"?(isNaN(date)?defaultDate:offsetNumeric(date)):date)));date=(date&&date.toString()=="Invalid Date"?defaultDate:date);if(date){date.setHours(0);date.setMinutes(0);date.setSeconds(0);date.setMilliseconds(0)}return this._daylightSavingAdjust(date)},_daylightSavingAdjust:function(date){if(!date){return null}date.setHours(date.getHours()>12?date.getHours()+2:0);return date},_setDate:function(inst,date,endDate){var clear=!(date);var origMonth=inst.selectedMonth;var origYear=inst.selectedYear;date=this._determineDate(date,new Date());inst.selectedDay=inst.currentDay=date.getDate();inst.drawMonth=inst.selectedMonth=inst.currentMonth=date.getMonth();inst.drawYear=inst.selectedYear=inst.currentYear=date.getFullYear();if(origMonth!=inst.selectedMonth||origYear!=inst.selectedYear){this._notifyChange(inst)}this._adjustInstDate(inst);if(inst.input){inst.input.val(clear?"":this._formatDate(inst))}},_getDate:function(inst){var startDate=(!inst.currentYear||(inst.input&&inst.input.val()=="")?null:this._daylightSavingAdjust(new Date(inst.currentYear,inst.currentMonth,inst.currentDay)));return startDate},_generateHTML:function(inst){var today=new Date();today=this._daylightSavingAdjust(new Date(today.getFullYear(),today.getMonth(),today.getDate()));var isRTL=this._get(inst,"isRTL");var showButtonPanel=this._get(inst,"showButtonPanel");var hideIfNoPrevNext=this._get(inst,"hideIfNoPrevNext");var navigationAsDateFormat=this._get(inst,"navigationAsDateFormat");var numMonths=this._getNumberOfMonths(inst);var showCurrentAtPos=this._get(inst,"showCurrentAtPos");var stepMonths=this._get(inst,"stepMonths");var stepBigMonths=this._get(inst,"stepBigMonths");var isMultiMonth=(numMonths[0]!=1||numMonths[1]!=1);var currentDate=this._daylightSavingAdjust((!inst.currentDay?new Date(9999,9,9):new Date(inst.currentYear,inst.currentMonth,inst.currentDay)));var minDate=this._getMinMaxDate(inst,"min",true);var maxDate=this._getMinMaxDate(inst,"max");var drawMonth=inst.drawMonth-showCurrentAtPos;var drawYear=inst.drawYear;if(drawMonth<0){drawMonth+=12;drawYear--}if(maxDate){var maxDraw=this._daylightSavingAdjust(new Date(maxDate.getFullYear(),maxDate.getMonth()-numMonths[1]+1,maxDate.getDate()));maxDraw=(minDate&&maxDraw<minDate?minDate:maxDraw);while(this._daylightSavingAdjust(new Date(drawYear,drawMonth,1))>maxDraw){drawMonth--;if(drawMonth<0){drawMonth=11;drawYear--}}}inst.drawMonth=drawMonth;inst.drawYear=drawYear;var prevText=this._get(inst,"prevText");prevText=(!navigationAsDateFormat?prevText:this.formatDate(prevText,this._daylightSavingAdjust(new Date(drawYear,drawMonth-stepMonths,1)),this._getFormatConfig(inst)));var prev=(this._canAdjustMonth(inst,-1,drawYear,drawMonth)?'<a class="ui-datepicker-prev ui-corner-all" onclick="DP_jQuery.datepicker._adjustDate(\'#'+inst.id+"', -"+stepMonths+", 'M');\" title=\""+prevText+'"><span class="ui-icon ui-icon-circle-triangle-'+(isRTL?"e":"w")+'">'+prevText+"</span></a>":(hideIfNoPrevNext?"":'<a class="ui-datepicker-prev ui-corner-all ui-state-disabled" title="'+prevText+'"><span class="ui-icon ui-icon-circle-triangle-'+(isRTL?"e":"w")+'">'+prevText+"</span></a>"));var nextText=this._get(inst,"nextText");nextText=(!navigationAsDateFormat?nextText:this.formatDate(nextText,this._daylightSavingAdjust(new Date(drawYear,drawMonth+stepMonths,1)),this._getFormatConfig(inst)));var next=(this._canAdjustMonth(inst,+1,drawYear,drawMonth)?'<a class="ui-datepicker-next ui-corner-all" onclick="DP_jQuery.datepicker._adjustDate(\'#'+inst.id+"', +"+stepMonths+", 'M');\" title=\""+nextText+'"><span class="ui-icon ui-icon-circle-triangle-'+(isRTL?"w":"e")+'">'+nextText+"</span></a>":(hideIfNoPrevNext?"":'<a class="ui-datepicker-next ui-corner-all ui-state-disabled" title="'+nextText+'"><span class="ui-icon ui-icon-circle-triangle-'+(isRTL?"w":"e")+'">'+nextText+"</span></a>"));var currentText=this._get(inst,"currentText");var gotoDate=(this._get(inst,"gotoCurrent")&&inst.currentDay?currentDate:today);currentText=(!navigationAsDateFormat?currentText:this.formatDate(currentText,gotoDate,this._getFormatConfig(inst)));var controls=(!inst.inline?'<button type="button" class="ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all" onclick="DP_jQuery.datepicker._hideDatepicker();">'+this._get(inst,"closeText")+"</button>":"");var buttonPanel=(showButtonPanel)?'<div class="ui-datepicker-buttonpane ui-widget-content">'+(isRTL?controls:"")+(this._isInRange(inst,gotoDate)?'<button type="button" class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all" onclick="DP_jQuery.datepicker._gotoToday(\'#'+inst.id+"');\">"+currentText+"</button>":"")+(isRTL?"":controls)+"</div>":"";var firstDay=parseInt(this._get(inst,"firstDay"),10);firstDay=(isNaN(firstDay)?0:firstDay);var dayNames=this._get(inst,"dayNames");var dayNamesShort=this._get(inst,"dayNamesShort");var dayNamesMin=this._get(inst,"dayNamesMin");var monthNames=this._get(inst,"monthNames");var monthNamesShort=this._get(inst,"monthNamesShort");var beforeShowDay=this._get(inst,"beforeShowDay");var showOtherMonths=this._get(inst,"showOtherMonths");var calculateWeek=this._get(inst,"calculateWeek")||this.iso8601Week;var endDate=inst.endDay?this._daylightSavingAdjust(new Date(inst.endYear,inst.endMonth,inst.endDay)):currentDate;var defaultDate=this._getDefaultDate(inst);var html="";for(var row=0;row<numMonths[0];row++){var group="";for(var col=0;col<numMonths[1];col++){var selectedDate=this._daylightSavingAdjust(new Date(drawYear,drawMonth,inst.selectedDay));var cornerClass=" ui-corner-all";var calender="";if(isMultiMonth){calender+='<div class="ui-datepicker-group ui-datepicker-group-';switch(col){case 0:calender+="first";cornerClass=" ui-corner-"+(isRTL?"right":"left");break;case numMonths[1]-1:calender+="last";cornerClass=" ui-corner-"+(isRTL?"left":"right");break;default:calender+="middle";cornerClass="";break}calender+='">'}calender+='<div class="ui-datepicker-header ui-widget-header ui-helper-clearfix'+cornerClass+'">'+(/all|left/.test(cornerClass)&&row==0?(isRTL?next:prev):"")+(/all|right/.test(cornerClass)&&row==0?(isRTL?prev:next):"")+this._generateMonthYearHeader(inst,drawMonth,drawYear,minDate,maxDate,selectedDate,row>0||col>0,monthNames,monthNamesShort)+'</div><table class="ui-datepicker-calendar"><thead><tr>';var thead="";for(var dow=0;dow<7;dow++){var day=(dow+firstDay)%7;thead+="<th"+((dow+firstDay+6)%7>=5?' class="ui-datepicker-week-end"':"")+'><span title="'+dayNames[day]+'">'+dayNamesMin[day]+"</span></th>"}calender+=thead+"</tr></thead><tbody>";var daysInMonth=this._getDaysInMonth(drawYear,drawMonth);if(drawYear==inst.selectedYear&&drawMonth==inst.selectedMonth){inst.selectedDay=Math.min(inst.selectedDay,daysInMonth)}var leadDays=(this._getFirstDayOfMonth(drawYear,drawMonth)-firstDay+7)%7;var numRows=(isMultiMonth?6:Math.ceil((leadDays+daysInMonth)/7));var printDate=this._daylightSavingAdjust(new Date(drawYear,drawMonth,1-leadDays));for(var dRow=0;dRow<numRows;dRow++){calender+="<tr>";var tbody="";for(var dow=0;dow<7;dow++){var daySettings=(beforeShowDay?beforeShowDay.apply((inst.input?inst.input[0]:null),[printDate]):[true,""]);var otherMonth=(printDate.getMonth()!=drawMonth);var unselectable=otherMonth||!daySettings[0]||(minDate&&printDate<minDate)||(maxDate&&printDate>maxDate);tbody+='<td class="'+((dow+firstDay+6)%7>=5?" ui-datepicker-week-end":"")+(otherMonth?" ui-datepicker-other-month":"")+((printDate.getTime()==selectedDate.getTime()&&drawMonth==inst.selectedMonth&&inst._keyEvent)||(defaultDate.getTime()==printDate.getTime()&&defaultDate.getTime()==selectedDate.getTime())?" "+this._dayOverClass:"")+(unselectable?" "+this._unselectableClass+" ui-state-disabled":"")+(otherMonth&&!showOtherMonths?"":" "+daySettings[1]+(printDate.getTime()>=currentDate.getTime()&&printDate.getTime()<=endDate.getTime()?" "+this._currentClass:"")+(printDate.getTime()==today.getTime()?" ui-datepicker-today":""))+'"'+((!otherMonth||showOtherMonths)&&daySettings[2]?' title="'+daySettings[2]+'"':"")+(unselectable?"":" onclick=\"DP_jQuery.datepicker._selectDay('#"+inst.id+"',"+drawMonth+","+drawYear+', this);return false;"')+">"+(otherMonth?(showOtherMonths?printDate.getDate():"&#xa0;"):(unselectable?'<span class="ui-state-default">'+printDate.getDate()+"</span>":'<a class="ui-state-default'+(printDate.getTime()==today.getTime()?" ui-state-highlight":"")+(printDate.getTime()>=currentDate.getTime()&&printDate.getTime()<=endDate.getTime()?" ui-state-active":"")+'" href="#">'+printDate.getDate()+"</a>"))+"</td>";printDate.setDate(printDate.getDate()+1);printDate=this._daylightSavingAdjust(printDate)}calender+=tbody+"</tr>"}drawMonth++;if(drawMonth>11){drawMonth=0;drawYear++}calender+="</tbody></table>"+(isMultiMonth?"</div>"+((numMonths[0]>0&&col==numMonths[1]-1)?'<div class="ui-datepicker-row-break"></div>':""):"");group+=calender}html+=group}html+=buttonPanel+($.browser.msie&&parseInt($.browser.version,10)<7&&!inst.inline?'<iframe src="javascript:false;" class="ui-datepicker-cover" frameborder="0"></iframe>':"");inst._keyEvent=false;return html},_generateMonthYearHeader:function(inst,drawMonth,drawYear,minDate,maxDate,selectedDate,secondary,monthNames,monthNamesShort){minDate=(inst.rangeStart&&minDate&&selectedDate<minDate?selectedDate:minDate);var changeMonth=this._get(inst,"changeMonth");var changeYear=this._get(inst,"changeYear");var showMonthAfterYear=this._get(inst,"showMonthAfterYear");var html='<div class="ui-datepicker-title">';var monthHtml="";if(secondary||!changeMonth){monthHtml+='<span class="ui-datepicker-month">'+monthNames[drawMonth]+"</span> "}else{var inMinYear=(minDate&&minDate.getFullYear()==drawYear);var inMaxYear=(maxDate&&maxDate.getFullYear()==drawYear);monthHtml+='<select class="ui-datepicker-month" onchange="DP_jQuery.datepicker._selectMonthYear(\'#'+inst.id+"', this, 'M');\" onclick=\"DP_jQuery.datepicker._clickMonthYear('#"+inst.id+"');\">";for(var month=0;month<12;month++){if((!inMinYear||month>=minDate.getMonth())&&(!inMaxYear||month<=maxDate.getMonth())){monthHtml+='<option value="'+month+'"'+(month==drawMonth?' selected="selected"':"")+">"+monthNamesShort[month]+"</option>"}}monthHtml+="</select>"}if(!showMonthAfterYear){html+=monthHtml+((secondary||changeMonth||changeYear)&&(!(changeMonth&&changeYear))?"&#xa0;":"")}if(secondary||!changeYear){html+='<span class="ui-datepicker-year">'+drawYear+"</span>"}else{var years=this._get(inst,"yearRange").split(":");var year=0;var endYear=0;if(years.length!=2){year=drawYear-10;endYear=drawYear+10}else{if(years[0].charAt(0)=="+"||years[0].charAt(0)=="-"){year=drawYear+parseInt(years[0],10);endYear=drawYear+parseInt(years[1],10)}else{year=parseInt(years[0],10);endYear=parseInt(years[1],10)}}year=(minDate?Math.max(year,minDate.getFullYear()):year);endYear=(maxDate?Math.min(endYear,maxDate.getFullYear()):endYear);html+='<select class="ui-datepicker-year" onchange="DP_jQuery.datepicker._selectMonthYear(\'#'+inst.id+"', this, 'Y');\" onclick=\"DP_jQuery.datepicker._clickMonthYear('#"+inst.id+"');\">";for(;year<=endYear;year++){html+='<option value="'+year+'"'+(year==drawYear?' selected="selected"':"")+">"+year+"</option>"}html+="</select>"}if(showMonthAfterYear){html+=(secondary||changeMonth||changeYear?"&#xa0;":"")+monthHtml}html+="</div>";return html},_adjustInstDate:function(inst,offset,period){var year=inst.drawYear+(period=="Y"?offset:0);var month=inst.drawMonth+(period=="M"?offset:0);var day=Math.min(inst.selectedDay,this._getDaysInMonth(year,month))+(period=="D"?offset:0);var date=this._daylightSavingAdjust(new Date(year,month,day));var minDate=this._getMinMaxDate(inst,"min",true);var maxDate=this._getMinMaxDate(inst,"max");date=(minDate&&date<minDate?minDate:date);date=(maxDate&&date>maxDate?maxDate:date);inst.selectedDay=date.getDate();inst.drawMonth=inst.selectedMonth=date.getMonth();inst.drawYear=inst.selectedYear=date.getFullYear();if(period=="M"||period=="Y"){this._notifyChange(inst)}},_notifyChange:function(inst){var onChange=this._get(inst,"onChangeMonthYear");if(onChange){onChange.apply((inst.input?inst.input[0]:null),[inst.selectedYear,inst.selectedMonth+1,inst])}},_getNumberOfMonths:function(inst){var numMonths=this._get(inst,"numberOfMonths");return(numMonths==null?[1,1]:(typeof numMonths=="number"?[1,numMonths]:numMonths))},_getMinMaxDate:function(inst,minMax,checkRange){var date=this._determineDate(this._get(inst,minMax+"Date"),null);return(!checkRange||!inst.rangeStart?date:(!date||inst.rangeStart>date?inst.rangeStart:date))},_getDaysInMonth:function(year,month){return 32-new Date(year,month,32).getDate()},_getFirstDayOfMonth:function(year,month){return new Date(year,month,1).getDay()},_canAdjustMonth:function(inst,offset,curYear,curMonth){var numMonths=this._getNumberOfMonths(inst);var date=this._daylightSavingAdjust(new Date(curYear,curMonth+(offset<0?offset:numMonths[1]),1));if(offset<0){date.setDate(this._getDaysInMonth(date.getFullYear(),date.getMonth()))}return this._isInRange(inst,date)},_isInRange:function(inst,date){var newMinDate=(!inst.rangeStart?null:this._daylightSavingAdjust(new Date(inst.selectedYear,inst.selectedMonth,inst.selectedDay)));newMinDate=(newMinDate&&inst.rangeStart<newMinDate?inst.rangeStart:newMinDate);var minDate=newMinDate||this._getMinMaxDate(inst,"min");var maxDate=this._getMinMaxDate(inst,"max");return((!minDate||date>=minDate)&&(!maxDate||date<=maxDate))},_getFormatConfig:function(inst){var shortYearCutoff=this._get(inst,"shortYearCutoff");shortYearCutoff=(typeof shortYearCutoff!="string"?shortYearCutoff:new Date().getFullYear()%100+parseInt(shortYearCutoff,10));return{shortYearCutoff:shortYearCutoff,dayNamesShort:this._get(inst,"dayNamesShort"),dayNames:this._get(inst,"dayNames"),monthNamesShort:this._get(inst,"monthNamesShort"),monthNames:this._get(inst,"monthNames")}},_formatDate:function(inst,day,month,year){if(!day){inst.currentDay=inst.selectedDay;inst.currentMonth=inst.selectedMonth;inst.currentYear=inst.selectedYear}var date=(day?(typeof day=="object"?day:this._daylightSavingAdjust(new Date(year,month,day))):this._daylightSavingAdjust(new Date(inst.currentYear,inst.currentMonth,inst.currentDay)));return this.formatDate(this._get(inst,"dateFormat"),date,this._getFormatConfig(inst))}});function extendRemove(target,props){$.extend(target,props);for(var name in props){if(props[name]==null||props[name]==undefined){target[name]=props[name]}}return target}function isArray(a){return(a&&(($.browser.safari&&typeof a=="object"&&a.length)||(a.constructor&&a.constructor.toString().match(/\Array\(\)/))))}$.fn.datepicker=function(options){if(!$.datepicker.initialized){$(document).mousedown($.datepicker._checkExternalClick).find("body").append($.datepicker.dpDiv);$.datepicker.initialized=true}var otherArgs=Array.prototype.slice.call(arguments,1);if(typeof options=="string"&&(options=="isDisabled"||options=="getDate")){return $.datepicker["_"+options+"Datepicker"].apply($.datepicker,[this[0]].concat(otherArgs))}if(options=="option"&&arguments.length==2&&typeof arguments[1]=="string"){return $.datepicker["_"+options+"Datepicker"].apply($.datepicker,[this[0]].concat(otherArgs))}return this.each(function(){typeof options=="string"?$.datepicker["_"+options+"Datepicker"].apply($.datepicker,[this].concat(otherArgs)):$.datepicker._attachDatepicker(this,options)})};$.datepicker=new Datepicker();$.datepicker.initialized=false;$.datepicker.uuid=new Date().getTime();$.datepicker.version="1.7.2";window.DP_jQuery=$})(jQuery);;/*
 * jQuery UI Progressbar 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Progressbar
 *
 * Depends:
 *   ui.core.js
 */
(function(a){a.widget("ui.progressbar",{_init:function(){this.element.addClass("ui-progressbar ui-widget ui-widget-content ui-corner-all").attr({role:"progressbar","aria-valuemin":this._valueMin(),"aria-valuemax":this._valueMax(),"aria-valuenow":this._value()});this.valueDiv=a('<div class="ui-progressbar-value ui-widget-header ui-corner-left"></div>').appendTo(this.element);this._refreshValue()},destroy:function(){this.element.removeClass("ui-progressbar ui-widget ui-widget-content ui-corner-all").removeAttr("role").removeAttr("aria-valuemin").removeAttr("aria-valuemax").removeAttr("aria-valuenow").removeData("progressbar").unbind(".progressbar");this.valueDiv.remove();a.widget.prototype.destroy.apply(this,arguments)},value:function(b){if(b===undefined){return this._value()}this._setData("value",b);return this},_setData:function(b,c){switch(b){case"value":this.options.value=c;this._refreshValue();this._trigger("change",null,{});break}a.widget.prototype._setData.apply(this,arguments)},_value:function(){var b=this.options.value;if(b<this._valueMin()){b=this._valueMin()}if(b>this._valueMax()){b=this._valueMax()}return b},_valueMin:function(){var b=0;return b},_valueMax:function(){var b=100;return b},_refreshValue:function(){var b=this.value();this.valueDiv[b==this._valueMax()?"addClass":"removeClass"]("ui-corner-right");this.valueDiv.width(b+"%");this.element.attr("aria-valuenow",b)}});a.extend(a.ui.progressbar,{version:"1.7.2",defaults:{value:0}})})(jQuery);;/*
 * jQuery UI Effects 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Effects/
 */
jQuery.effects||(function(d){d.effects={version:"1.7.2",save:function(g,h){for(var f=0;f<h.length;f++){if(h[f]!==null){g.data("ec.storage."+h[f],g[0].style[h[f]])}}},restore:function(g,h){for(var f=0;f<h.length;f++){if(h[f]!==null){g.css(h[f],g.data("ec.storage."+h[f]))}}},setMode:function(f,g){if(g=="toggle"){g=f.is(":hidden")?"show":"hide"}return g},getBaseline:function(g,h){var i,f;switch(g[0]){case"top":i=0;break;case"middle":i=0.5;break;case"bottom":i=1;break;default:i=g[0]/h.height}switch(g[1]){case"left":f=0;break;case"center":f=0.5;break;case"right":f=1;break;default:f=g[1]/h.width}return{x:f,y:i}},createWrapper:function(f){if(f.parent().is(".ui-effects-wrapper")){return f.parent()}var g={width:f.outerWidth(true),height:f.outerHeight(true),"float":f.css("float")};f.wrap('<div class="ui-effects-wrapper" style="font-size:100%;background:transparent;border:none;margin:0;padding:0"></div>');var j=f.parent();if(f.css("position")=="static"){j.css({position:"relative"});f.css({position:"relative"})}else{var i=f.css("top");if(isNaN(parseInt(i,10))){i="auto"}var h=f.css("left");if(isNaN(parseInt(h,10))){h="auto"}j.css({position:f.css("position"),top:i,left:h,zIndex:f.css("z-index")}).show();f.css({position:"relative",top:0,left:0})}j.css(g);return j},removeWrapper:function(f){if(f.parent().is(".ui-effects-wrapper")){return f.parent().replaceWith(f)}return f},setTransition:function(g,i,f,h){h=h||{};d.each(i,function(k,j){unit=g.cssUnit(j);if(unit[0]>0){h[j]=unit[0]*f+unit[1]}});return h},animateClass:function(h,i,k,j){var f=(typeof k=="function"?k:(j?j:null));var g=(typeof k=="string"?k:null);return this.each(function(){var q={};var o=d(this);var p=o.attr("style")||"";if(typeof p=="object"){p=p.cssText}if(h.toggle){o.hasClass(h.toggle)?h.remove=h.toggle:h.add=h.toggle}var l=d.extend({},(document.defaultView?document.defaultView.getComputedStyle(this,null):this.currentStyle));if(h.add){o.addClass(h.add)}if(h.remove){o.removeClass(h.remove)}var m=d.extend({},(document.defaultView?document.defaultView.getComputedStyle(this,null):this.currentStyle));if(h.add){o.removeClass(h.add)}if(h.remove){o.addClass(h.remove)}for(var r in m){if(typeof m[r]!="function"&&m[r]&&r.indexOf("Moz")==-1&&r.indexOf("length")==-1&&m[r]!=l[r]&&(r.match(/color/i)||(!r.match(/color/i)&&!isNaN(parseInt(m[r],10))))&&(l.position!="static"||(l.position=="static"&&!r.match(/left|top|bottom|right/)))){q[r]=m[r]}}o.animate(q,i,g,function(){if(typeof d(this).attr("style")=="object"){d(this).attr("style")["cssText"]="";d(this).attr("style")["cssText"]=p}else{d(this).attr("style",p)}if(h.add){d(this).addClass(h.add)}if(h.remove){d(this).removeClass(h.remove)}if(f){f.apply(this,arguments)}})})}};function c(g,f){var i=g[1]&&g[1].constructor==Object?g[1]:{};if(f){i.mode=f}var h=g[1]&&g[1].constructor!=Object?g[1]:(i.duration?i.duration:g[2]);h=d.fx.off?0:typeof h==="number"?h:d.fx.speeds[h]||d.fx.speeds._default;var j=i.callback||(d.isFunction(g[1])&&g[1])||(d.isFunction(g[2])&&g[2])||(d.isFunction(g[3])&&g[3]);return[g[0],i,h,j]}d.fn.extend({_show:d.fn.show,_hide:d.fn.hide,__toggle:d.fn.toggle,_addClass:d.fn.addClass,_removeClass:d.fn.removeClass,_toggleClass:d.fn.toggleClass,effect:function(g,f,h,i){return d.effects[g]?d.effects[g].call(this,{method:g,options:f||{},duration:h,callback:i}):null},show:function(){if(!arguments[0]||(arguments[0].constructor==Number||(/(slow|normal|fast)/).test(arguments[0]))){return this._show.apply(this,arguments)}else{return this.effect.apply(this,c(arguments,"show"))}},hide:function(){if(!arguments[0]||(arguments[0].constructor==Number||(/(slow|normal|fast)/).test(arguments[0]))){return this._hide.apply(this,arguments)}else{return this.effect.apply(this,c(arguments,"hide"))}},toggle:function(){if(!arguments[0]||(arguments[0].constructor==Number||(/(slow|normal|fast)/).test(arguments[0]))||(d.isFunction(arguments[0])||typeof arguments[0]=="boolean")){return this.__toggle.apply(this,arguments)}else{return this.effect.apply(this,c(arguments,"toggle"))}},addClass:function(g,f,i,h){return f?d.effects.animateClass.apply(this,[{add:g},f,i,h]):this._addClass(g)},removeClass:function(g,f,i,h){return f?d.effects.animateClass.apply(this,[{remove:g},f,i,h]):this._removeClass(g)},toggleClass:function(g,f,i,h){return((typeof f!=="boolean")&&f)?d.effects.animateClass.apply(this,[{toggle:g},f,i,h]):this._toggleClass(g,f)},morph:function(f,h,g,j,i){return d.effects.animateClass.apply(this,[{add:h,remove:f},g,j,i])},switchClass:function(){return this.morph.apply(this,arguments)},cssUnit:function(f){var g=this.css(f),h=[];d.each(["em","px","%","pt"],function(j,k){if(g.indexOf(k)>0){h=[parseFloat(g),k]}});return h}});d.each(["backgroundColor","borderBottomColor","borderLeftColor","borderRightColor","borderTopColor","color","outlineColor"],function(g,f){d.fx.step[f]=function(h){if(h.state==0){h.start=e(h.elem,f);h.end=b(h.end)}h.elem.style[f]="rgb("+[Math.max(Math.min(parseInt((h.pos*(h.end[0]-h.start[0]))+h.start[0],10),255),0),Math.max(Math.min(parseInt((h.pos*(h.end[1]-h.start[1]))+h.start[1],10),255),0),Math.max(Math.min(parseInt((h.pos*(h.end[2]-h.start[2]))+h.start[2],10),255),0)].join(",")+")"}});function b(g){var f;if(g&&g.constructor==Array&&g.length==3){return g}if(f=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(g)){return[parseInt(f[1],10),parseInt(f[2],10),parseInt(f[3],10)]}if(f=/rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(g)){return[parseFloat(f[1])*2.55,parseFloat(f[2])*2.55,parseFloat(f[3])*2.55]}if(f=/#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(g)){return[parseInt(f[1],16),parseInt(f[2],16),parseInt(f[3],16)]}if(f=/#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(g)){return[parseInt(f[1]+f[1],16),parseInt(f[2]+f[2],16),parseInt(f[3]+f[3],16)]}if(f=/rgba\(0, 0, 0, 0\)/.exec(g)){return a.transparent}return a[d.trim(g).toLowerCase()]}function e(h,f){var g;do{g=d.curCSS(h,f);if(g!=""&&g!="transparent"||d.nodeName(h,"body")){break}f="backgroundColor"}while(h=h.parentNode);return b(g)}var a={aqua:[0,255,255],azure:[240,255,255],beige:[245,245,220],black:[0,0,0],blue:[0,0,255],brown:[165,42,42],cyan:[0,255,255],darkblue:[0,0,139],darkcyan:[0,139,139],darkgrey:[169,169,169],darkgreen:[0,100,0],darkkhaki:[189,183,107],darkmagenta:[139,0,139],darkolivegreen:[85,107,47],darkorange:[255,140,0],darkorchid:[153,50,204],darkred:[139,0,0],darksalmon:[233,150,122],darkviolet:[148,0,211],fuchsia:[255,0,255],gold:[255,215,0],green:[0,128,0],indigo:[75,0,130],khaki:[240,230,140],lightblue:[173,216,230],lightcyan:[224,255,255],lightgreen:[144,238,144],lightgrey:[211,211,211],lightpink:[255,182,193],lightyellow:[255,255,224],lime:[0,255,0],magenta:[255,0,255],maroon:[128,0,0],navy:[0,0,128],olive:[128,128,0],orange:[255,165,0],pink:[255,192,203],purple:[128,0,128],violet:[128,0,128],red:[255,0,0],silver:[192,192,192],white:[255,255,255],yellow:[255,255,0],transparent:[255,255,255]};d.easing.jswing=d.easing.swing;d.extend(d.easing,{def:"easeOutQuad",swing:function(g,h,f,j,i){return d.easing[d.easing.def](g,h,f,j,i)},easeInQuad:function(g,h,f,j,i){return j*(h/=i)*h+f},easeOutQuad:function(g,h,f,j,i){return -j*(h/=i)*(h-2)+f},easeInOutQuad:function(g,h,f,j,i){if((h/=i/2)<1){return j/2*h*h+f}return -j/2*((--h)*(h-2)-1)+f},easeInCubic:function(g,h,f,j,i){return j*(h/=i)*h*h+f},easeOutCubic:function(g,h,f,j,i){return j*((h=h/i-1)*h*h+1)+f},easeInOutCubic:function(g,h,f,j,i){if((h/=i/2)<1){return j/2*h*h*h+f}return j/2*((h-=2)*h*h+2)+f},easeInQuart:function(g,h,f,j,i){return j*(h/=i)*h*h*h+f},easeOutQuart:function(g,h,f,j,i){return -j*((h=h/i-1)*h*h*h-1)+f},easeInOutQuart:function(g,h,f,j,i){if((h/=i/2)<1){return j/2*h*h*h*h+f}return -j/2*((h-=2)*h*h*h-2)+f},easeInQuint:function(g,h,f,j,i){return j*(h/=i)*h*h*h*h+f},easeOutQuint:function(g,h,f,j,i){return j*((h=h/i-1)*h*h*h*h+1)+f},easeInOutQuint:function(g,h,f,j,i){if((h/=i/2)<1){return j/2*h*h*h*h*h+f}return j/2*((h-=2)*h*h*h*h+2)+f},easeInSine:function(g,h,f,j,i){return -j*Math.cos(h/i*(Math.PI/2))+j+f},easeOutSine:function(g,h,f,j,i){return j*Math.sin(h/i*(Math.PI/2))+f},easeInOutSine:function(g,h,f,j,i){return -j/2*(Math.cos(Math.PI*h/i)-1)+f},easeInExpo:function(g,h,f,j,i){return(h==0)?f:j*Math.pow(2,10*(h/i-1))+f},easeOutExpo:function(g,h,f,j,i){return(h==i)?f+j:j*(-Math.pow(2,-10*h/i)+1)+f},easeInOutExpo:function(g,h,f,j,i){if(h==0){return f}if(h==i){return f+j}if((h/=i/2)<1){return j/2*Math.pow(2,10*(h-1))+f}return j/2*(-Math.pow(2,-10*--h)+2)+f},easeInCirc:function(g,h,f,j,i){return -j*(Math.sqrt(1-(h/=i)*h)-1)+f},easeOutCirc:function(g,h,f,j,i){return j*Math.sqrt(1-(h=h/i-1)*h)+f},easeInOutCirc:function(g,h,f,j,i){if((h/=i/2)<1){return -j/2*(Math.sqrt(1-h*h)-1)+f}return j/2*(Math.sqrt(1-(h-=2)*h)+1)+f},easeInElastic:function(g,i,f,m,l){var j=1.70158;var k=0;var h=m;if(i==0){return f}if((i/=l)==1){return f+m}if(!k){k=l*0.3}if(h<Math.abs(m)){h=m;var j=k/4}else{var j=k/(2*Math.PI)*Math.asin(m/h)}return -(h*Math.pow(2,10*(i-=1))*Math.sin((i*l-j)*(2*Math.PI)/k))+f},easeOutElastic:function(g,i,f,m,l){var j=1.70158;var k=0;var h=m;if(i==0){return f}if((i/=l)==1){return f+m}if(!k){k=l*0.3}if(h<Math.abs(m)){h=m;var j=k/4}else{var j=k/(2*Math.PI)*Math.asin(m/h)}return h*Math.pow(2,-10*i)*Math.sin((i*l-j)*(2*Math.PI)/k)+m+f},easeInOutElastic:function(g,i,f,m,l){var j=1.70158;var k=0;var h=m;if(i==0){return f}if((i/=l/2)==2){return f+m}if(!k){k=l*(0.3*1.5)}if(h<Math.abs(m)){h=m;var j=k/4}else{var j=k/(2*Math.PI)*Math.asin(m/h)}if(i<1){return -0.5*(h*Math.pow(2,10*(i-=1))*Math.sin((i*l-j)*(2*Math.PI)/k))+f}return h*Math.pow(2,-10*(i-=1))*Math.sin((i*l-j)*(2*Math.PI)/k)*0.5+m+f},easeInBack:function(g,h,f,k,j,i){if(i==undefined){i=1.70158}return k*(h/=j)*h*((i+1)*h-i)+f},easeOutBack:function(g,h,f,k,j,i){if(i==undefined){i=1.70158}return k*((h=h/j-1)*h*((i+1)*h+i)+1)+f},easeInOutBack:function(g,h,f,k,j,i){if(i==undefined){i=1.70158}if((h/=j/2)<1){return k/2*(h*h*(((i*=(1.525))+1)*h-i))+f}return k/2*((h-=2)*h*(((i*=(1.525))+1)*h+i)+2)+f},easeInBounce:function(g,h,f,j,i){return j-d.easing.easeOutBounce(g,i-h,0,j,i)+f},easeOutBounce:function(g,h,f,j,i){if((h/=i)<(1/2.75)){return j*(7.5625*h*h)+f}else{if(h<(2/2.75)){return j*(7.5625*(h-=(1.5/2.75))*h+0.75)+f}else{if(h<(2.5/2.75)){return j*(7.5625*(h-=(2.25/2.75))*h+0.9375)+f}else{return j*(7.5625*(h-=(2.625/2.75))*h+0.984375)+f}}}},easeInOutBounce:function(g,h,f,j,i){if(h<i/2){return d.easing.easeInBounce(g,h*2,0,j,i)*0.5+f}return d.easing.easeOutBounce(g,h*2-i,0,j,i)*0.5+j*0.5+f}})})(jQuery);;/*
 * jQuery UI Effects Blind 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Effects/Blind
 *
 * Depends:
 *	effects.core.js
 */
(function(a){a.effects.blind=function(b){return this.queue(function(){var d=a(this),c=["position","top","left"];var h=a.effects.setMode(d,b.options.mode||"hide");var g=b.options.direction||"vertical";a.effects.save(d,c);d.show();var j=a.effects.createWrapper(d).css({overflow:"hidden"});var e=(g=="vertical")?"height":"width";var i=(g=="vertical")?j.height():j.width();if(h=="show"){j.css(e,0)}var f={};f[e]=h=="show"?i:0;j.animate(f,b.duration,b.options.easing,function(){if(h=="hide"){d.hide()}a.effects.restore(d,c);a.effects.removeWrapper(d);if(b.callback){b.callback.apply(d[0],arguments)}d.dequeue()})})}})(jQuery);;/*
 * jQuery UI Effects Bounce 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Effects/Bounce
 *
 * Depends:
 *	effects.core.js
 */
(function(a){a.effects.bounce=function(b){return this.queue(function(){var e=a(this),l=["position","top","left"];var k=a.effects.setMode(e,b.options.mode||"effect");var n=b.options.direction||"up";var c=b.options.distance||20;var d=b.options.times||5;var g=b.duration||250;if(/show|hide/.test(k)){l.push("opacity")}a.effects.save(e,l);e.show();a.effects.createWrapper(e);var f=(n=="up"||n=="down")?"top":"left";var p=(n=="up"||n=="left")?"pos":"neg";var c=b.options.distance||(f=="top"?e.outerHeight({margin:true})/3:e.outerWidth({margin:true})/3);if(k=="show"){e.css("opacity",0).css(f,p=="pos"?-c:c)}if(k=="hide"){c=c/(d*2)}if(k!="hide"){d--}if(k=="show"){var h={opacity:1};h[f]=(p=="pos"?"+=":"-=")+c;e.animate(h,g/2,b.options.easing);c=c/2;d--}for(var j=0;j<d;j++){var o={},m={};o[f]=(p=="pos"?"-=":"+=")+c;m[f]=(p=="pos"?"+=":"-=")+c;e.animate(o,g/2,b.options.easing).animate(m,g/2,b.options.easing);c=(k=="hide")?c*2:c/2}if(k=="hide"){var h={opacity:0};h[f]=(p=="pos"?"-=":"+=")+c;e.animate(h,g/2,b.options.easing,function(){e.hide();a.effects.restore(e,l);a.effects.removeWrapper(e);if(b.callback){b.callback.apply(this,arguments)}})}else{var o={},m={};o[f]=(p=="pos"?"-=":"+=")+c;m[f]=(p=="pos"?"+=":"-=")+c;e.animate(o,g/2,b.options.easing).animate(m,g/2,b.options.easing,function(){a.effects.restore(e,l);a.effects.removeWrapper(e);if(b.callback){b.callback.apply(this,arguments)}})}e.queue("fx",function(){e.dequeue()});e.dequeue()})}})(jQuery);;/*
 * jQuery UI Effects Clip 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Effects/Clip
 *
 * Depends:
 *	effects.core.js
 */
(function(a){a.effects.clip=function(b){return this.queue(function(){var f=a(this),j=["position","top","left","height","width"];var i=a.effects.setMode(f,b.options.mode||"hide");var k=b.options.direction||"vertical";a.effects.save(f,j);f.show();var c=a.effects.createWrapper(f).css({overflow:"hidden"});var e=f[0].tagName=="IMG"?c:f;var g={size:(k=="vertical")?"height":"width",position:(k=="vertical")?"top":"left"};var d=(k=="vertical")?e.height():e.width();if(i=="show"){e.css(g.size,0);e.css(g.position,d/2)}var h={};h[g.size]=i=="show"?d:0;h[g.position]=i=="show"?0:d/2;e.animate(h,{queue:false,duration:b.duration,easing:b.options.easing,complete:function(){if(i=="hide"){f.hide()}a.effects.restore(f,j);a.effects.removeWrapper(f);if(b.callback){b.callback.apply(f[0],arguments)}f.dequeue()}})})}})(jQuery);;/*
 * jQuery UI Effects Drop 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Effects/Drop
 *
 * Depends:
 *	effects.core.js
 */
(function(a){a.effects.drop=function(b){return this.queue(function(){var e=a(this),d=["position","top","left","opacity"];var i=a.effects.setMode(e,b.options.mode||"hide");var h=b.options.direction||"left";a.effects.save(e,d);e.show();a.effects.createWrapper(e);var f=(h=="up"||h=="down")?"top":"left";var c=(h=="up"||h=="left")?"pos":"neg";var j=b.options.distance||(f=="top"?e.outerHeight({margin:true})/2:e.outerWidth({margin:true})/2);if(i=="show"){e.css("opacity",0).css(f,c=="pos"?-j:j)}var g={opacity:i=="show"?1:0};g[f]=(i=="show"?(c=="pos"?"+=":"-="):(c=="pos"?"-=":"+="))+j;e.animate(g,{queue:false,duration:b.duration,easing:b.options.easing,complete:function(){if(i=="hide"){e.hide()}a.effects.restore(e,d);a.effects.removeWrapper(e);if(b.callback){b.callback.apply(this,arguments)}e.dequeue()}})})}})(jQuery);;/*
 * jQuery UI Effects Explode 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Effects/Explode
 *
 * Depends:
 *	effects.core.js
 */
(function(a){a.effects.explode=function(b){return this.queue(function(){var k=b.options.pieces?Math.round(Math.sqrt(b.options.pieces)):3;var e=b.options.pieces?Math.round(Math.sqrt(b.options.pieces)):3;b.options.mode=b.options.mode=="toggle"?(a(this).is(":visible")?"hide":"show"):b.options.mode;var h=a(this).show().css("visibility","hidden");var l=h.offset();l.top-=parseInt(h.css("marginTop"),10)||0;l.left-=parseInt(h.css("marginLeft"),10)||0;var g=h.outerWidth(true);var c=h.outerHeight(true);for(var f=0;f<k;f++){for(var d=0;d<e;d++){h.clone().appendTo("body").wrap("<div></div>").css({position:"absolute",visibility:"visible",left:-d*(g/e),top:-f*(c/k)}).parent().addClass("ui-effects-explode").css({position:"absolute",overflow:"hidden",width:g/e,height:c/k,left:l.left+d*(g/e)+(b.options.mode=="show"?(d-Math.floor(e/2))*(g/e):0),top:l.top+f*(c/k)+(b.options.mode=="show"?(f-Math.floor(k/2))*(c/k):0),opacity:b.options.mode=="show"?0:1}).animate({left:l.left+d*(g/e)+(b.options.mode=="show"?0:(d-Math.floor(e/2))*(g/e)),top:l.top+f*(c/k)+(b.options.mode=="show"?0:(f-Math.floor(k/2))*(c/k)),opacity:b.options.mode=="show"?1:0},b.duration||500)}}setTimeout(function(){b.options.mode=="show"?h.css({visibility:"visible"}):h.css({visibility:"visible"}).hide();if(b.callback){b.callback.apply(h[0])}h.dequeue();a("div.ui-effects-explode").remove()},b.duration||500)})}})(jQuery);;/*
 * jQuery UI Effects Fold 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Effects/Fold
 *
 * Depends:
 *	effects.core.js
 */
(function(a){a.effects.fold=function(b){return this.queue(function(){var e=a(this),k=["position","top","left"];var h=a.effects.setMode(e,b.options.mode||"hide");var o=b.options.size||15;var n=!(!b.options.horizFirst);var g=b.duration?b.duration/2:a.fx.speeds._default/2;a.effects.save(e,k);e.show();var d=a.effects.createWrapper(e).css({overflow:"hidden"});var i=((h=="show")!=n);var f=i?["width","height"]:["height","width"];var c=i?[d.width(),d.height()]:[d.height(),d.width()];var j=/([0-9]+)%/.exec(o);if(j){o=parseInt(j[1],10)/100*c[h=="hide"?0:1]}if(h=="show"){d.css(n?{height:0,width:o}:{height:o,width:0})}var m={},l={};m[f[0]]=h=="show"?c[0]:o;l[f[1]]=h=="show"?c[1]:0;d.animate(m,g,b.options.easing).animate(l,g,b.options.easing,function(){if(h=="hide"){e.hide()}a.effects.restore(e,k);a.effects.removeWrapper(e);if(b.callback){b.callback.apply(e[0],arguments)}e.dequeue()})})}})(jQuery);;/*
 * jQuery UI Effects Highlight 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Effects/Highlight
 *
 * Depends:
 *	effects.core.js
 */
(function(a){a.effects.highlight=function(b){return this.queue(function(){var e=a(this),d=["backgroundImage","backgroundColor","opacity"];var h=a.effects.setMode(e,b.options.mode||"show");var c=b.options.color||"#ffff99";var g=e.css("backgroundColor");a.effects.save(e,d);e.show();e.css({backgroundImage:"none",backgroundColor:c});var f={backgroundColor:g};if(h=="hide"){f.opacity=0}e.animate(f,{queue:false,duration:b.duration,easing:b.options.easing,complete:function(){if(h=="hide"){e.hide()}a.effects.restore(e,d);if(h=="show"&&a.browser.msie){this.style.removeAttribute("filter")}if(b.callback){b.callback.apply(this,arguments)}e.dequeue()}})})}})(jQuery);;/*
 * jQuery UI Effects Pulsate 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Effects/Pulsate
 *
 * Depends:
 *	effects.core.js
 */
(function(a){a.effects.pulsate=function(b){return this.queue(function(){var d=a(this);var g=a.effects.setMode(d,b.options.mode||"show");var f=b.options.times||5;var e=b.duration?b.duration/2:a.fx.speeds._default/2;if(g=="hide"){f--}if(d.is(":hidden")){d.css("opacity",0);d.show();d.animate({opacity:1},e,b.options.easing);f=f-2}for(var c=0;c<f;c++){d.animate({opacity:0},e,b.options.easing).animate({opacity:1},e,b.options.easing)}if(g=="hide"){d.animate({opacity:0},e,b.options.easing,function(){d.hide();if(b.callback){b.callback.apply(this,arguments)}})}else{d.animate({opacity:0},e,b.options.easing).animate({opacity:1},e,b.options.easing,function(){if(b.callback){b.callback.apply(this,arguments)}})}d.queue("fx",function(){d.dequeue()});d.dequeue()})}})(jQuery);;/*
 * jQuery UI Effects Scale 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Effects/Scale
 *
 * Depends:
 *	effects.core.js
 */
(function(a){a.effects.puff=function(b){return this.queue(function(){var f=a(this);var c=a.extend(true,{},b.options);var h=a.effects.setMode(f,b.options.mode||"hide");var g=parseInt(b.options.percent,10)||150;c.fade=true;var e={height:f.height(),width:f.width()};var d=g/100;f.from=(h=="hide")?e:{height:e.height*d,width:e.width*d};c.from=f.from;c.percent=(h=="hide")?g:100;c.mode=h;f.effect("scale",c,b.duration,b.callback);f.dequeue()})};a.effects.scale=function(b){return this.queue(function(){var g=a(this);var d=a.extend(true,{},b.options);var j=a.effects.setMode(g,b.options.mode||"effect");var h=parseInt(b.options.percent,10)||(parseInt(b.options.percent,10)==0?0:(j=="hide"?0:100));var i=b.options.direction||"both";var c=b.options.origin;if(j!="effect"){d.origin=c||["middle","center"];d.restore=true}var f={height:g.height(),width:g.width()};g.from=b.options.from||(j=="show"?{height:0,width:0}:f);var e={y:i!="horizontal"?(h/100):1,x:i!="vertical"?(h/100):1};g.to={height:f.height*e.y,width:f.width*e.x};if(b.options.fade){if(j=="show"){g.from.opacity=0;g.to.opacity=1}if(j=="hide"){g.from.opacity=1;g.to.opacity=0}}d.from=g.from;d.to=g.to;d.mode=j;g.effect("size",d,b.duration,b.callback);g.dequeue()})};a.effects.size=function(b){return this.queue(function(){var c=a(this),n=["position","top","left","width","height","overflow","opacity"];var m=["position","top","left","overflow","opacity"];var j=["width","height","overflow"];var p=["fontSize"];var k=["borderTopWidth","borderBottomWidth","paddingTop","paddingBottom"];var f=["borderLeftWidth","borderRightWidth","paddingLeft","paddingRight"];var g=a.effects.setMode(c,b.options.mode||"effect");var i=b.options.restore||false;var e=b.options.scale||"both";var o=b.options.origin;var d={height:c.height(),width:c.width()};c.from=b.options.from||d;c.to=b.options.to||d;if(o){var h=a.effects.getBaseline(o,d);c.from.top=(d.height-c.from.height)*h.y;c.from.left=(d.width-c.from.width)*h.x;c.to.top=(d.height-c.to.height)*h.y;c.to.left=(d.width-c.to.width)*h.x}var l={from:{y:c.from.height/d.height,x:c.from.width/d.width},to:{y:c.to.height/d.height,x:c.to.width/d.width}};if(e=="box"||e=="both"){if(l.from.y!=l.to.y){n=n.concat(k);c.from=a.effects.setTransition(c,k,l.from.y,c.from);c.to=a.effects.setTransition(c,k,l.to.y,c.to)}if(l.from.x!=l.to.x){n=n.concat(f);c.from=a.effects.setTransition(c,f,l.from.x,c.from);c.to=a.effects.setTransition(c,f,l.to.x,c.to)}}if(e=="content"||e=="both"){if(l.from.y!=l.to.y){n=n.concat(p);c.from=a.effects.setTransition(c,p,l.from.y,c.from);c.to=a.effects.setTransition(c,p,l.to.y,c.to)}}a.effects.save(c,i?n:m);c.show();a.effects.createWrapper(c);c.css("overflow","hidden").css(c.from);if(e=="content"||e=="both"){k=k.concat(["marginTop","marginBottom"]).concat(p);f=f.concat(["marginLeft","marginRight"]);j=n.concat(k).concat(f);c.find("*[width]").each(function(){child=a(this);if(i){a.effects.save(child,j)}var q={height:child.height(),width:child.width()};child.from={height:q.height*l.from.y,width:q.width*l.from.x};child.to={height:q.height*l.to.y,width:q.width*l.to.x};if(l.from.y!=l.to.y){child.from=a.effects.setTransition(child,k,l.from.y,child.from);child.to=a.effects.setTransition(child,k,l.to.y,child.to)}if(l.from.x!=l.to.x){child.from=a.effects.setTransition(child,f,l.from.x,child.from);child.to=a.effects.setTransition(child,f,l.to.x,child.to)}child.css(child.from);child.animate(child.to,b.duration,b.options.easing,function(){if(i){a.effects.restore(child,j)}})})}c.animate(c.to,{queue:false,duration:b.duration,easing:b.options.easing,complete:function(){if(g=="hide"){c.hide()}a.effects.restore(c,i?n:m);a.effects.removeWrapper(c);if(b.callback){b.callback.apply(this,arguments)}c.dequeue()}})})}})(jQuery);;/*
 * jQuery UI Effects Shake 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Effects/Shake
 *
 * Depends:
 *	effects.core.js
 */
(function(a){a.effects.shake=function(b){return this.queue(function(){var e=a(this),l=["position","top","left"];var k=a.effects.setMode(e,b.options.mode||"effect");var n=b.options.direction||"left";var c=b.options.distance||20;var d=b.options.times||3;var g=b.duration||b.options.duration||140;a.effects.save(e,l);e.show();a.effects.createWrapper(e);var f=(n=="up"||n=="down")?"top":"left";var p=(n=="up"||n=="left")?"pos":"neg";var h={},o={},m={};h[f]=(p=="pos"?"-=":"+=")+c;o[f]=(p=="pos"?"+=":"-=")+c*2;m[f]=(p=="pos"?"-=":"+=")+c*2;e.animate(h,g,b.options.easing);for(var j=1;j<d;j++){e.animate(o,g,b.options.easing).animate(m,g,b.options.easing)}e.animate(o,g,b.options.easing).animate(h,g/2,b.options.easing,function(){a.effects.restore(e,l);a.effects.removeWrapper(e);if(b.callback){b.callback.apply(this,arguments)}});e.queue("fx",function(){e.dequeue()});e.dequeue()})}})(jQuery);;/*
 * jQuery UI Effects Slide 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Effects/Slide
 *
 * Depends:
 *	effects.core.js
 */
(function(a){a.effects.slide=function(b){return this.queue(function(){var e=a(this),d=["position","top","left"];var i=a.effects.setMode(e,b.options.mode||"show");var h=b.options.direction||"left";a.effects.save(e,d);e.show();a.effects.createWrapper(e).css({overflow:"hidden"});var f=(h=="up"||h=="down")?"top":"left";var c=(h=="up"||h=="left")?"pos":"neg";var j=b.options.distance||(f=="top"?e.outerHeight({margin:true}):e.outerWidth({margin:true}));if(i=="show"){e.css(f,c=="pos"?-j:j)}var g={};g[f]=(i=="show"?(c=="pos"?"+=":"-="):(c=="pos"?"-=":"+="))+j;e.animate(g,{queue:false,duration:b.duration,easing:b.options.easing,complete:function(){if(i=="hide"){e.hide()}a.effects.restore(e,d);a.effects.removeWrapper(e);if(b.callback){b.callback.apply(this,arguments)}e.dequeue()}})})}})(jQuery);;/*
 * jQuery UI Effects Transfer 1.7.2
 *
 * Copyright (c) 2009 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Effects/Transfer
 *
 * Depends:
 *	effects.core.js
 */
(function(a){a.effects.transfer=function(b){return this.queue(function(){var f=a(this),h=a(b.options.to),e=h.offset(),g={top:e.top,left:e.left,height:h.innerHeight(),width:h.innerWidth()},d=f.offset(),c=a('<div class="ui-effects-transfer"></div>').appendTo(document.body).addClass(b.options.className).css({top:d.top,left:d.left,height:f.innerHeight(),width:f.innerWidth(),position:"absolute"}).animate(g,b.duration,b.options.easing,function(){c.remove();(b.callback&&b.callback.apply(f[0],arguments));f.dequeue()})})}})(jQuery);;;/*
 * Flexigrid for jQuery - New Wave Grid
 *
 * Copyright (c) 2008 Paulo P. Marinas (webplicity.net/flexigrid)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * $Date: 2008-07-14 00:09:43 +0800 (Tue, 14 Jul 2008) $
 */
 
(function($){
		  
	$.addFlex = function(t,p)
	{

		if (t.grid) return false; //return if already exist	
		
		// apply default properties
		p = $.extend({
			 height: 200, //default height
			 width: 'auto', //auto width
			 striped: true, //apply odd even stripes
			 novstripe: false,
			 minwidth: 30, //min width of columns
			 minheight: 80, //min height of columns
			 resizable: true, //resizable table
			 url: false, //ajax url
			 method: 'POST', // data sending method
			 dataType: 'xml', // type of data loaded
			 errormsg: 'Connection Error',
			 usepager: false, //
			 nowrap: true, //
			 page: 1, //current page
			 total: 1, //total pages
			 useRp: true, //use the results per page select box
			 rp: 15, // results per page
			 rpOptions: [10,15,20,25,40],
			 title: false,
			 pagestat: 'Displaying {from} to {to} of {total} items',
			 procmsg: 'Processing, please wait ...',
			 query: '',
			 qtype: '',
			 nomsg: 'No items',
			 minColToggle: 1, //minimum allowed column to be hidden
			 showToggleBtn: true, //show or hide column toggle popup
			 hideOnSubmit: true,
			 autoload: true,
			 blockOpacity: 0.5,
			 onToggleCol: false,
			 onChangeSort: false,
			 onSuccess: false,
			 onSubmit: false // using a custom populate function
		  }, p);
		  		

		$(t)
		.show() //show if hidden
		.attr({cellPadding: 0, cellSpacing: 0, border: 0})  //remove padding and spacing
		.removeAttr('width') //remove width properties	
		;
		
		//create grid class
		var g = {
			hset : {},
			rePosDrag: function () {

			var cdleft = 0 - this.hDiv.scrollLeft;
			if (this.hDiv.scrollLeft>0) cdleft -= Math.floor(p.cgwidth/2);
			$(g.cDrag).css({top:g.hDiv.offsetTop+1});
			var cdpad = this.cdpad;
			
			$('div',g.cDrag).hide();
			
			$('thead tr:first th:visible',this.hDiv).each
				(
			 	function ()
					{
					var n = $('thead tr:first th:visible',g.hDiv).index(this);

					var cdpos = parseInt($('div',this).width());
					var ppos = cdpos;
					if (cdleft==0) 
							cdleft -= Math.floor(p.cgwidth/2); 

					cdpos = cdpos + cdleft + cdpad;
					
					$('div:eq('+n+')',g.cDrag).css({'left':cdpos+'px'}).show();

					cdleft = cdpos;
					}
				);
				
			},
			fixHeight: function (newH) {
					newH = false;
					if (!newH) newH = $(g.bDiv).height();
					var hdHeight = $(this.hDiv).height();
					$('div',this.cDrag).each(
						function ()
							{
								$(this).height(newH+hdHeight);
							}
					);
					
					var nd = parseInt($(g.nDiv).height());
					
					if (nd>newH)
						$(g.nDiv).height(newH).width(200);
					else
						$(g.nDiv).height('auto').width('auto');
					
					$(g.block).css({height:newH,marginBottom:(newH * -1)});
					
					var hrH = g.bDiv.offsetTop + newH;
					if (p.height != 'auto' && p.resizable) hrH = g.vDiv.offsetTop;
					$(g.rDiv).css({height: hrH});
				
			},
			dragStart: function (dragtype,e,obj) { //default drag function start
				
				if (dragtype=='colresize') //column resize
					{
						$(g.nDiv).hide();$(g.nBtn).hide();
						var n = $('div',this.cDrag).index(obj);
						var ow = $('th:visible div:eq('+n+')',this.hDiv).width();
						$(obj).addClass('dragging').siblings().hide();
						$(obj).prev().addClass('dragging').show();
						
						this.colresize = {startX: e.pageX, ol: parseInt(obj.style.left), ow: ow, n : n };
						$('body').css('cursor','col-resize');
					}
				else if (dragtype=='vresize') //table resize
					{
						var hgo = false;
						$('body').css('cursor','row-resize');
						if (obj) 
							{
							hgo = true;
							$('body').css('cursor','col-resize');
							}
						this.vresize = {h: p.height, sy: e.pageY, w: p.width, sx: e.pageX, hgo: hgo};
						
					}

				else if (dragtype=='colMove') //column header drag
					{
						$(g.nDiv).hide();$(g.nBtn).hide();
						this.hset = $(this.hDiv).offset();
						this.hset.right = this.hset.left + $('table',this.hDiv).width();
						this.hset.bottom = this.hset.top + $('table',this.hDiv).height();
						this.dcol = obj;
						this.dcoln = $('th',this.hDiv).index(obj);
						
						this.colCopy = document.createElement("div");
						this.colCopy.className = "colCopy";
						this.colCopy.innerHTML = obj.innerHTML;
						if ($.browser.msie)
						{
						this.colCopy.className = "colCopy ie";
						}
						
						
						$(this.colCopy).css({position:'absolute',float:'left',display:'none', textAlign: obj.align});
						$('body').append(this.colCopy);
						$(this.cDrag).hide();
						
					}
														
				$('body').noSelect();
			
			},
			dragMove: function (e) {
			
				if (this.colresize) //column resize
					{
						var n = this.colresize.n;
						var diff = e.pageX-this.colresize.startX;
						var nleft = this.colresize.ol + diff;
						var nw = this.colresize.ow + diff;
						if (nw > p.minwidth)
							{
								$('div:eq('+n+')',this.cDrag).css('left',nleft);
								this.colresize.nw = nw;
							}
					}
				else if (this.vresize) //table resize
					{
						var v = this.vresize;
						var y = e.pageY;
						var diff = y-v.sy;
						
						if (!p.defwidth) p.defwidth = p.width;
						
						if (p.width != 'auto' && !p.nohresize && v.hgo)
						{
							var x = e.pageX;
							var xdiff = x - v.sx;
							var newW = v.w + xdiff;
							if (newW > p.defwidth)
								{
									this.gDiv.style.width = newW + 'px';
									p.width = newW;
								}
						}
						
						var newH = v.h + diff;
						if ((newH > p.minheight || p.height < p.minheight) && !v.hgo)
							{
								this.bDiv.style.height = newH + 'px';
								p.height = newH;
								this.fixHeight(newH);
							}
						v = null;
					}
				else if (this.colCopy) {
					$(this.dcol).addClass('thMove').removeClass('thOver'); 
					if (e.pageX > this.hset.right || e.pageX < this.hset.left || e.pageY > this.hset.bottom || e.pageY < this.hset.top)
					{
						//this.dragEnd();
						$('body').css('cursor','move');
					}
					else 
					$('body').css('cursor','pointer');
					$(this.colCopy).css({top:e.pageY + 10,left:e.pageX + 20, display: 'block'});
				}													
			
			},
			dragEnd: function () {

				if (this.colresize)
					{
						var n = this.colresize.n;
						var nw = this.colresize.nw;

								$('th:visible div:eq('+n+')',this.hDiv).css('width',nw);
								$('tr',this.bDiv).each (
									function ()
										{
										$('td:visible div:eq('+n+')',this).css('width',nw);
										}
								);
								this.hDiv.scrollLeft = this.bDiv.scrollLeft;


						$('div:eq('+n+')',this.cDrag).siblings().show();
						$('.dragging',this.cDrag).removeClass('dragging');
						this.rePosDrag();
						this.fixHeight();
						this.colresize = false;
					}
				else if (this.vresize)
					{
						this.vresize = false;
					}
				else if (this.colCopy)
					{
						$(this.colCopy).remove();
						if (this.dcolt != null)
							{
							
							
							if (this.dcoln>this.dcolt)
								
								$('th:eq('+this.dcolt+')',this.hDiv).before(this.dcol);
							else
								$('th:eq('+this.dcolt+')',this.hDiv).after(this.dcol);
							
							
							
							this.switchCol(this.dcoln,this.dcolt);
							$(this.cdropleft).remove();
							$(this.cdropright).remove();
							this.rePosDrag();
							
																			
							}
						
						this.dcol = null;
						this.hset = null;
						this.dcoln = null;
						this.dcolt = null;
						this.colCopy = null;
						
						$('.thMove',this.hDiv).removeClass('thMove');
						$(this.cDrag).show();
					}										
				$('body').css('cursor','default');
				$('body').noSelect(false);
			},
			toggleCol: function(cid,visible) {
				
				var ncol = $("th[axis='col"+cid+"']",this.hDiv)[0];
				var n = $('thead th',g.hDiv).index(ncol);
				var cb = $('input[value='+cid+']',g.nDiv)[0];
				
				
				if (visible==null)
					{
						visible = ncol.hide;
					}
				
				
				
				if ($('input:checked',g.nDiv).length<p.minColToggle&&!visible) return false;
				
				if (visible)
					{
						ncol.hide = false;
						$(ncol).show();
						cb.checked = true;
					}
				else
					{
						ncol.hide = true;
						$(ncol).hide();
						cb.checked = false;
					}
					
						$('tbody tr',t).each
							(
								function ()
									{
										if (visible)
											$('td:eq('+n+')',this).show();
										else
											$('td:eq('+n+')',this).hide();
									}
							);							
				
				this.rePosDrag();
				
				if (p.onToggleCol) p.onToggleCol(cid,visible);
				
				return visible;
			},
			switchCol: function(cdrag,cdrop) { //switch columns
				
				$('tbody tr',t).each
					(
						function ()
							{
								if (cdrag>cdrop)
									$('td:eq('+cdrop+')',this).before($('td:eq('+cdrag+')',this));
								else
									$('td:eq('+cdrop+')',this).after($('td:eq('+cdrag+')',this));
							}
					);
					
					//switch order in nDiv
					if (cdrag>cdrop)
						$('tr:eq('+cdrop+')',this.nDiv).before($('tr:eq('+cdrag+')',this.nDiv));
					else
						$('tr:eq('+cdrop+')',this.nDiv).after($('tr:eq('+cdrag+')',this.nDiv));
						
					if ($.browser.msie&&$.browser.version<7.0) $('tr:eq('+cdrop+') input',this.nDiv)[0].checked = true;	
					
					this.hDiv.scrollLeft = this.bDiv.scrollLeft;
			},			
			scroll: function() {
					this.hDiv.scrollLeft = this.bDiv.scrollLeft;
					this.rePosDrag();
			},
			addData: function (data) { //parse data
				
				if (p.preProcess)
					data = p.preProcess(data);
				
				$('.pReload',this.pDiv).removeClass('loading');
				this.loading = false;

				if (!data) 
					{
					$('.pPageStat',this.pDiv).html(p.errormsg);	
					return false;
					}

				if (p.dataType=='xml')
					p.total = +$('rows total',data).text();
				else
					p.total = data.total;
					
				if (p.total==0)
					{
					$('tr, a, td, div',t).unbind();
					$(t).empty();
					p.pages = 1;
					p.page = 1;
					this.buildpager();
					$('.pPageStat',this.pDiv).html(p.nomsg);
					return false;
					}
				
				p.pages = Math.ceil(p.total/p.rp);
				
				if (p.dataType=='xml')
					p.page = +$('rows page',data).text();
				else
					p.page = data.page;
				
				this.buildpager();

				//build new body
				var tbody = document.createElement('tbody');
				
				if (p.dataType=='json')
				{
					$.each
					(
					 data.rows,
					 function(i,row) 
					 	{
							var tr = document.createElement('tr');
							if (i % 2 && p.striped) tr.className = 'erow';
							
							if (row.id) tr.id = 'row' + row.id;
							
							//add cell
							$('thead tr:first th',g.hDiv).each
							(
							 	function ()
									{
										
										var td = document.createElement('td');
										var idx = $(this).attr('axis').substr(3);
										td.align = this.align;
										td.innerHTML = row.cell[idx];
										$(tr).append(td);
										td = null;
									}
							); 
							
							
							if ($('thead',this.gDiv).length<1) //handle if grid has no headers
							{

									for (idx=0;idx<cell.length;idx++)
										{
										var td = document.createElement('td');
										td.innerHTML = row.cell[idx];
										$(tr).append(td);
										td = null;
										}
							}							
							
							$(tbody).append(tr);
							tr = null;
						}
					);				
					
				} else if (p.dataType=='xml') {

				i = 1;

				$("rows row",data).each
				(
				 
				 	function ()
						{
							
							i++;
							
							var tr = document.createElement('tr');
							if (i % 2 && p.striped) tr.className = 'erow';

							var nid =$(this).attr('id');
							if (nid) tr.id = 'row' + nid;
							
							nid = null;
							
							var robj = this;

							
							
							$('thead tr:first th',g.hDiv).each
							(
							 	function ()
									{
										
										var td = document.createElement('td');
										var idx = $(this).attr('axis').substr(3);
										td.align = this.align;
										td.innerHTML = $("cell:eq("+ idx +")",robj).text();
										$(tr).append(td);
										td = null;
									}
							);
							
							
							if ($('thead',this.gDiv).length<1) //handle if grid has no headers
							{
								$('cell',this).each
								(
								 	function ()
										{
										var td = document.createElement('td');
										td.innerHTML = $(this).text();
										$(tr).append(td);
										td = null;
										}
								);
							}
							
							$(tbody).append(tr);
							tr = null;
							robj = null;
						}
				);
				
				}

				$('tr',t).unbind();
				$(t).empty();
				
				$(t).append(tbody);
				this.addCellProp();
				this.addRowProp();
				
				//this.fixHeight($(this.bDiv).height());
				
				this.rePosDrag();
				
				tbody = null; data = null; i = null; 
				
				if (p.onSuccess) p.onSuccess();
				if (p.hideOnSubmit) $(g.block).remove();//$(t).show();
				
				this.hDiv.scrollLeft = this.bDiv.scrollLeft;
				if ($.browser.opera) $(t).css('visibility','visible');
				
			},
			changeSort: function(th) { //change sortorder
			
				if (this.loading) return true;
				
				$(g.nDiv).hide();$(g.nBtn).hide();
				
				if (p.sortname == $(th).attr('abbr'))
					{
						if (p.sortorder=='asc') p.sortorder = 'desc'; 
						else p.sortorder = 'asc';						
					}
				
				$(th).addClass('sorted').siblings().removeClass('sorted');
				$('.sdesc',this.hDiv).removeClass('sdesc');
				$('.sasc',this.hDiv).removeClass('sasc');
				$('div',th).addClass('s'+p.sortorder);
				p.sortname= $(th).attr('abbr');
				
				if (p.onChangeSort)
					p.onChangeSort(p.sortname,p.sortorder);
				else
					this.populate();				
			
			},
			buildpager: function(){ //rebuild pager based on new properties
			
			$('.pcontrol input',this.pDiv).val(p.page);
			$('.pcontrol span',this.pDiv).html(p.pages);
			
			var r1 = (p.page-1) * p.rp + 1; 
			var r2 = r1 + p.rp - 1; 
			
			if (p.total<r2) r2 = p.total;
			
			var stat = p.pagestat;
			
			stat = stat.replace(/{from}/,r1);
			stat = stat.replace(/{to}/,r2);
			stat = stat.replace(/{total}/,p.total);
			
			$('.pPageStat',this.pDiv).html(stat);
			
			},
			populate: function () { //get latest data

				if (this.loading) return true;

				if (p.onSubmit)
					{
						var gh = p.onSubmit();
						if (!gh) return false;
					}

				this.loading = true;
				if (!p.url) return false;
				
				$('.pPageStat',this.pDiv).html(p.procmsg);
				
				$('.pReload',this.pDiv).addClass('loading');
				
				$(g.block).css({top:g.bDiv.offsetTop});
				
				if (p.hideOnSubmit) $(this.gDiv).prepend(g.block); //$(t).hide();
				
				if ($.browser.opera) $(t).css('visibility','hidden');
				
				if (!p.newp) p.newp = 1;
				
				if (p.page>p.pages) p.page = p.pages;
				//var param = {page:p.newp, rp: p.rp, sortname: p.sortname, sortorder: p.sortorder, query: p.query, qtype: p.qtype};
				var param = [
					 { name : 'page', value : p.newp }
					,{ name : 'rp', value : p.rp }
					,{ name : 'sortname', value : p.sortname}
					,{ name : 'sortorder', value : p.sortorder }
					,{ name : 'query', value : p.query}
					,{ name : 'qtype', value : p.qtype}
				];							 
							 
				if (p.params)
					{
						for (var pi = 0; pi < p.params.length; pi++) param[param.length] = p.params[pi];
					}
				
					$.ajax({
					   type: p.method,
					   url: p.url,
					   data: param,
					   dataType: p.dataType,
					   success: function(data){g.addData(data);},
					   error: function(data) { try { if (p.onError) p.onError(data); } catch (e) {} }
					 });
			},
			doSearch: function () {
				p.query = $('input[name=q]',g.sDiv).val();
				p.qtype = $('select[name=qtype]',g.sDiv).val();
				p.newp = 1;

				this.populate();				
			},
			changePage: function (ctype){ //change page
			
				if (this.loading) return true;
			
				switch(ctype)
				{
					case 'first': p.newp = 1; break;
					case 'prev': if (p.page>1) p.newp = parseInt(p.page) - 1; break;
					case 'next': if (p.page<p.pages) p.newp = parseInt(p.page) + 1; break;
					case 'last': p.newp = p.pages; break;
					case 'input': 
							var nv = parseInt($('.pcontrol input',this.pDiv).val());
							if (isNaN(nv)) nv = 1;
							if (nv<1) nv = 1;
							else if (nv > p.pages) nv = p.pages;
							$('.pcontrol input',this.pDiv).val(nv);
							p.newp =nv;
							break;
				}
			
				if (p.newp==p.page) return false;
				
				if (p.onChangePage) 
					p.onChangePage(p.newp);
				else				
					this.populate();
			
			},
			addCellProp: function ()
			{
				
					$('tbody tr td',g.bDiv).each
					(
						function ()
							{
									var tdDiv = document.createElement('div');
									var n = $('td',$(this).parent()).index(this);
									var pth = $('th:eq('+n+')',g.hDiv).get(0);
			
									if (pth!=null)
									{
									if (p.sortname==$(pth).attr('abbr')&&p.sortname) 
										{
										this.className = 'sorted';
										}
									 $(tdDiv).css({textAlign:pth.align,width: $('div:first',pth)[0].style.width});
									 
									 if (pth.hide) $(this).css('display','none');
									 
									 }
									 
									 if (p.nowrap==false) $(tdDiv).css('white-space','normal');
									 
									 if (this.innerHTML=='') this.innerHTML = '&nbsp;';
									 
									 //tdDiv.value = this.innerHTML; //store preprocess value
									 tdDiv.innerHTML = this.innerHTML;
									 
									 var prnt = $(this).parent()[0];
									 var pid = false;
									 if (prnt.id) pid = prnt.id.substr(3);
									 
									 if (pth!=null)
									 {
									 if (pth.process) pth.process(tdDiv,pid);
									 }
									 
									$(this).empty().append(tdDiv).removeAttr('width'); //wrap content

									//add editable event here 'dblclick'

							}
					);
					
			},
			getCellDim: function (obj) // get cell prop for editable event
			{
				var ht = parseInt($(obj).height());
				var pht = parseInt($(obj).parent().height());
				var wt = parseInt(obj.style.width);
				var pwt = parseInt($(obj).parent().width());
				var top = obj.offsetParent.offsetTop;
				var left = obj.offsetParent.offsetLeft;
				var pdl = parseInt($(obj).css('paddingLeft'));
				var pdt = parseInt($(obj).css('paddingTop'));
				return {ht:ht,wt:wt,top:top,left:left,pdl:pdl, pdt:pdt, pht:pht, pwt: pwt};
			},
			addRowProp: function()
			{
					$('tbody tr',g.bDiv).each
					(
						function ()
							{
							$(this)
							.click(
								function (e) 
									{ 
										var obj = (e.target || e.srcElement); if (obj.href || obj.type) return true;
										//we should set selected class - kurund
										//$(this).toggleClass('trSelected');
										if (p.singleSelect) $(this).siblings().removeClass('trSelected');
									}
							)
							.mousedown(
								function (e)
									{
										if (e.shiftKey)
										{
										$(this).toggleClass('trSelected'); 
										g.multisel = true; 
										this.focus();
										$(g.gDiv).noSelect();
										}
									}
							)
							.mouseup(
								function ()
									{
										if (g.multisel)
										{
										g.multisel = false;
										$(g.gDiv).noSelect(false);
										}
									}
							)
							.hover(
								function (e) 
									{ 
									if (g.multisel) 
										{
										$(this).toggleClass('trSelected'); 
										}
									},
								function () {}						
							)
							;
							
							if ($.browser.msie&&$.browser.version<7.0)
								{
									$(this)
									.hover(
										function () { $(this).addClass('trOver'); },
										function () { $(this).removeClass('trOver'); }
									)
									;
								}
							}
					);
					
					
			},
			pager: 0
			};		
		
		//create model if any
		if (p.colModel)
		{
			thead = document.createElement('thead');
			tr = document.createElement('tr');
			
			for (i=0;i<p.colModel.length;i++)
				{
					var cm = p.colModel[i];
					var th = document.createElement('th');

					th.innerHTML = cm.display;
					
					if (cm.name&&cm.sortable)
						$(th).attr('abbr',cm.name);
					
					//th.idx = i;
					$(th).attr('axis','col'+i);
					
					if (cm.align)
						th.align = cm.align;
						
					if (cm.width) 
						$(th).attr('width',cm.width);

					if (cm.hide)
						{
						th.hide = true;
						}
						
					if (cm.process)
						{
							th.process = cm.process;
						}

					$(tr).append(th);
				}
			$(thead).append(tr);
			$(t).prepend(thead);
		} // end if p.colmodel	

		//init divs
		g.gDiv = document.createElement('div'); //create global container
		g.mDiv = document.createElement('div'); //create title container
		g.hDiv = document.createElement('div'); //create header container
		g.bDiv = document.createElement('div'); //create body container
		g.vDiv = document.createElement('div'); //create grip
		g.rDiv = document.createElement('div'); //create horizontal resizer
		g.cDrag = document.createElement('div'); //create column drag
		g.block = document.createElement('div'); //creat blocker
		g.nDiv = document.createElement('div'); //create column show/hide popup
		g.nBtn = document.createElement('div'); //create column show/hide button
		g.iDiv = document.createElement('div'); //create editable layer
		g.tDiv = document.createElement('div'); //create toolbar
		g.sDiv = document.createElement('div');
		
		if (p.usepager) g.pDiv = document.createElement('div'); //create pager container
		g.hTable = document.createElement('table');

		//set gDiv
		g.gDiv.className = 'flexigrid';
		if (p.width!='auto') g.gDiv.style.width = p.width + 'px';

		//add conditional classes
		if ($.browser.msie)
			$(g.gDiv).addClass('ie');
		
		if (p.novstripe)
			$(g.gDiv).addClass('novstripe');

		$(t).before(g.gDiv);
		$(g.gDiv)
		.append(t)
		;

		//set toolbar
		if (p.buttons) 
		{
			g.tDiv.className = 'tDiv';
			var tDiv2 = document.createElement('div');
			tDiv2.className = 'tDiv2';
			
			for (i=0;i<p.buttons.length;i++)
				{
					var btn = p.buttons[i];
					if (!btn.separator)
					{
						var btnDiv = document.createElement('div');
						btnDiv.className = 'fbutton';
						btnDiv.innerHTML = "<div><span>"+btn.name+"</span></div>";
						if (btn.bclass) 
							$('span',btnDiv)
							.addClass(btn.bclass)
							.css({paddingLeft:20})
							;
						btnDiv.onpress = btn.onpress;
						btnDiv.name = btn.name;
						if (btn.onpress)
						{
							$(btnDiv).click
							(	
								function () 
								{
								this.onpress(this.name,g.gDiv);
								}
							);
						}
						$(tDiv2).append(btnDiv);
						if ($.browser.msie&&$.browser.version<7.0)
						{
							$(btnDiv).hover(function(){$(this).addClass('fbOver');},function(){$(this).removeClass('fbOver');});
						}
						
					} else {
						$(tDiv2).append("<div class='btnseparator'></div>");
					}
				}
				$(g.tDiv).append(tDiv2);
				$(g.tDiv).append("<div style='clear:both'></div>");
				$(g.gDiv).prepend(g.tDiv);
		}
		
		//set hDiv
		g.hDiv.className = 'hDiv';

		$(t).before(g.hDiv);

		//set hTable
			g.hTable.cellPadding = 0;
			g.hTable.cellSpacing = 0;
			$(g.hDiv).append('<div class="hDivBox"></div>');
			$('div',g.hDiv).append(g.hTable);
			var thead = $("thead:first",t).get(0);
			if (thead) $(g.hTable).append(thead);
			thead = null;
		
		if (!p.colmodel) var ci = 0;

		//setup thead			
			$('thead tr:first th',g.hDiv).each
			(
			 	function ()
					{
						var thdiv = document.createElement('div');
						
						
					
						if ($(this).attr('abbr'))
							{
							$(this).click(
								function (e) 
									{
										
										if (!$(this).hasClass('thOver')) return false;
										var obj = (e.target || e.srcElement);
										if (obj.href || obj.type) return true; 
										g.changeSort(this);
									}
							)
							;
							
							if ($(this).attr('abbr')==p.sortname)
								{
								this.className = 'sorted';
								thdiv.className = 's'+p.sortorder;
								}
							}
							
							if (this.hide) $(this).hide();
							
							if (!p.colmodel)
							{
								$(this).attr('axis','col' + ci++);
							}
							
							
						 $(thdiv).css({textAlign:this.align, width: this.width + 'px'});
						 thdiv.innerHTML = this.innerHTML;
						 
						$(this).empty().append(thdiv).removeAttr('width')
						.mousedown(function (e) 
							{
								g.dragStart('colMove',e,this);
							})
						.hover(
							function(){
								if (!g.colresize&&!$(this).hasClass('thMove')&&!g.colCopy) $(this).addClass('thOver');
								
								if ($(this).attr('abbr')!=p.sortname&&!g.colCopy&&!g.colresize&&$(this).attr('abbr')) $('div',this).addClass('s'+p.sortorder);
								else if ($(this).attr('abbr')==p.sortname&&!g.colCopy&&!g.colresize&&$(this).attr('abbr'))
									{
										var no = '';
										if (p.sortorder=='asc') no = 'desc';
										else no = 'asc';
										$('div',this).removeClass('s'+p.sortorder).addClass('s'+no);
									}
								
								if (g.colCopy) 
									{
									var n = $('th',g.hDiv).index(this);
									
									if (n==g.dcoln) return false;
									
									
									
									if (n<g.dcoln) $(this).append(g.cdropleft);
									else $(this).append(g.cdropright);
									
									g.dcolt = n;
									
									} else if (!g.colresize) {
										
									var nv = $('th:visible',g.hDiv).index(this);
									var onl = parseInt($('div:eq('+nv+')',g.cDrag).css('left'));
									var nw = parseInt($(g.nBtn).width()) + parseInt($(g.nBtn).css('borderLeftWidth'));
									nl = onl - nw + Math.floor(p.cgwidth/2);
									
									$(g.nDiv).hide();$(g.nBtn).hide();
									
									$(g.nBtn).css({'left':nl,top:g.hDiv.offsetTop}).show();
									
									var ndw = parseInt($(g.nDiv).width());
									
									$(g.nDiv).css({top:g.bDiv.offsetTop});
									
									if ((nl+ndw)>$(g.gDiv).width())
										$(g.nDiv).css('left',onl-ndw+1);
									else
										$(g.nDiv).css('left',nl);
										
									if ($(this).hasClass('sorted')) 
										$(g.nBtn).addClass('srtd');
									else
										$(g.nBtn).removeClass('srtd');
										
									}
									
							},
							function(){
								$(this).removeClass('thOver');
								if ($(this).attr('abbr')!=p.sortname) $('div',this).removeClass('s'+p.sortorder);
								else if ($(this).attr('abbr')==p.sortname)
									{
										var no = '';
										if (p.sortorder=='asc') no = 'desc';
										else no = 'asc';
										
										$('div',this).addClass('s'+p.sortorder).removeClass('s'+no);
									}
								if (g.colCopy) 
									{								
									$(g.cdropleft).remove();
									$(g.cdropright).remove();
									g.dcolt = null;
									}
							})
						; //wrap content
					}
			);

		//set bDiv
		g.bDiv.className = 'bDiv';
		$(t).before(g.bDiv);
		$(g.bDiv)
		.css({ height: (p.height=='auto') ? 'auto' : p.height+"px"})
		.scroll(function (e) {g.scroll()})
		.append(t)
		;
		
		if (p.height == 'auto') 
			{
			$('table',g.bDiv).addClass('autoht');
			}


		//add td properties
		g.addCellProp();
		
		//add row properties
		g.addRowProp();
		
		//set cDrag
		
		var cdcol = $('thead tr:first th:first',g.hDiv).get(0);
		
		if (cdcol != null)
		{		
		g.cDrag.className = 'cDrag';
		g.cdpad = 0;
		
		g.cdpad += (isNaN(parseInt($('div',cdcol).css('borderLeftWidth'))) ? 0 : parseInt($('div',cdcol).css('borderLeftWidth'))); 
		g.cdpad += (isNaN(parseInt($('div',cdcol).css('borderRightWidth'))) ? 0 : parseInt($('div',cdcol).css('borderRightWidth'))); 
		g.cdpad += (isNaN(parseInt($('div',cdcol).css('paddingLeft'))) ? 0 : parseInt($('div',cdcol).css('paddingLeft'))); 
		g.cdpad += (isNaN(parseInt($('div',cdcol).css('paddingRight'))) ? 0 : parseInt($('div',cdcol).css('paddingRight'))); 
		g.cdpad += (isNaN(parseInt($(cdcol).css('borderLeftWidth'))) ? 0 : parseInt($(cdcol).css('borderLeftWidth'))); 
		g.cdpad += (isNaN(parseInt($(cdcol).css('borderRightWidth'))) ? 0 : parseInt($(cdcol).css('borderRightWidth'))); 
		g.cdpad += (isNaN(parseInt($(cdcol).css('paddingLeft'))) ? 0 : parseInt($(cdcol).css('paddingLeft'))); 
		g.cdpad += (isNaN(parseInt($(cdcol).css('paddingRight'))) ? 0 : parseInt($(cdcol).css('paddingRight'))); 

		$(g.bDiv).before(g.cDrag);
		
		var cdheight = $(g.bDiv).height();
		var hdheight = $(g.hDiv).height();
		
		$(g.cDrag).css({top: -hdheight + 'px'});
		
		$('thead tr:first th',g.hDiv).each
			(
			 	function ()
					{
						var cgDiv = document.createElement('div');
						$(g.cDrag).append(cgDiv);
						if (!p.cgwidth) p.cgwidth = $(cgDiv).width();
						$(cgDiv).css({height: cdheight + hdheight})
						.mousedown(function(e){g.dragStart('colresize',e,this);})
						;
						if ($.browser.msie&&$.browser.version<7.0)
						{
							g.fixHeight($(g.gDiv).height());
							$(cgDiv).hover(
								function () 
								{
								g.fixHeight();
								$(this).addClass('dragging') 
								},
								function () { if (!g.colresize) $(this).removeClass('dragging') }
							);
						}
					}
			);
		
		//g.rePosDrag();
							
		}
		

		//add strip		
		if (p.striped) 
			$('tbody tr:odd',g.bDiv).addClass('erow');
			
			
		if (p.resizable && p.height !='auto') 
		{
		g.vDiv.className = 'vGrip';
		$(g.vDiv)
		.mousedown(function (e) { g.dragStart('vresize',e)})
		.html('<span></span>');
		$(g.bDiv).after(g.vDiv);
		}
		
		if (p.resizable && p.width !='auto' && !p.nohresize) 
		{
		g.rDiv.className = 'hGrip';
		$(g.rDiv)
		.mousedown(function (e) {g.dragStart('vresize',e,true);})
		.html('<span></span>')
		.css('height',$(g.gDiv).height())
		;
		if ($.browser.msie&&$.browser.version<7.0)
		{
			$(g.rDiv).hover(function(){$(this).addClass('hgOver');},function(){$(this).removeClass('hgOver');});
		}
		$(g.gDiv).append(g.rDiv);
		}
		
		// add pager
		if (p.usepager)
		{
		g.pDiv.className = 'pDiv';
		g.pDiv.innerHTML = '<div class="pDiv2"></div>';
		$(g.bDiv).after(g.pDiv);
		var html = ' <div class="pGroup"> <div class="pFirst pButton"><span></span></div><div class="pPrev pButton"><span></span></div> </div> <div class="btnseparator"></div> <div class="pGroup"><span class="pcontrol">Page <input type="text" size="4" value="1" /> of <span> 1 </span></span></div> <div class="btnseparator"></div> <div class="pGroup"> <div class="pNext pButton"><span></span></div><div class="pLast pButton"><span></span></div> </div> <div class="btnseparator"></div> <div class="pGroup"> <div class="pReload pButton"><span></span></div> </div> <div class="btnseparator"></div> <div class="pGroup"><span class="pPageStat"></span></div>';
		$('div',g.pDiv).html(html);
		
		$('.pReload',g.pDiv).click(function(){g.populate()});
		$('.pFirst',g.pDiv).click(function(){g.changePage('first')});
		$('.pPrev',g.pDiv).click(function(){g.changePage('prev')});
		$('.pNext',g.pDiv).click(function(){g.changePage('next')});
		$('.pLast',g.pDiv).click(function(){g.changePage('last')});
		$('.pcontrol input',g.pDiv).keydown(function(e){if(e.keyCode==13) g.changePage('input')});
		if ($.browser.msie&&$.browser.version<7) $('.pButton',g.pDiv).hover(function(){$(this).addClass('pBtnOver');},function(){$(this).removeClass('pBtnOver');});
			
			if (p.useRp)
			{
			var opt = "";
			for (var nx=0;nx<p.rpOptions.length;nx++)
			{
				if (p.rp == p.rpOptions[nx]) sel = 'selected="selected"'; else sel = '';
				 opt += "<option value='" + p.rpOptions[nx] + "' " + sel + " >" + p.rpOptions[nx] + "&nbsp;&nbsp;</option>";
			};
			$('.pDiv2',g.pDiv).prepend("<div class='pGroup'><select name='rp'>"+opt+"</select></div> <div class='btnseparator'></div>");
			$('select',g.pDiv).change(
					function ()
					{
						if (p.onRpChange) 
							p.onRpChange(+this.value);
						else
							{
							p.newp = 1;
							p.rp = +this.value;
							g.populate();
							}
					}
				);
			}
		
		//add search button
		if (p.searchitems)
			{
				$('.pDiv2',g.pDiv).prepend("<div class='pGroup'> <div class='pSearch pButton'><span></span></div> </div>  <div class='btnseparator'></div>");
				$('.pSearch',g.pDiv).click(function(){$(g.sDiv).slideToggle('fast',function(){$('.sDiv:visible input:first',g.gDiv).trigger('focus');});});				
				//add search box
				g.sDiv.className = 'sDiv';
				
				sitems = p.searchitems;
				
				var sopt = "";
				for (var s = 0; s < sitems.length; s++)
				{
					if (p.qtype=='' && sitems[s].isdefault==true)
					{
					p.qtype = sitems[s].name;
					sel = 'selected="selected"';
					} else sel = '';
					sopt += "<option value='" + sitems[s].name + "' " + sel + " >" + sitems[s].display + "&nbsp;&nbsp;</option>";						
				}
				
				if (p.qtype=='') p.qtype = sitems[0].name;
				
				$(g.sDiv).append("<div class='sDiv2'>Quick Search <input type='text' size='30' name='q' class='qsbox' /> <select name='qtype'>"+sopt+"</select> <input type='button' value='Clear' /></div>");

				$('input[name=q],select[name=qtype]',g.sDiv).keydown(function(e){if(e.keyCode==13) g.doSearch()});
				$('input[value=Clear]',g.sDiv).click(function(){$('input[name=q]',g.sDiv).val(''); p.query = ''; g.doSearch(); });
				$(g.bDiv).after(g.sDiv);				
				
			}
		
		}
		$(g.pDiv,g.sDiv).append("<div style='clear:both'></div>");
	
		// add title
		if (p.title)
		{
			g.mDiv.className = 'mDiv';
			g.mDiv.innerHTML = '<div class="ftitle">'+p.title+'</div>';
			$(g.gDiv).prepend(g.mDiv);
			if (p.showTableToggleBtn)
				{
					$(g.mDiv).append('<div class="ptogtitle" title="Minimize/Maximize Table"><span></span></div>');
					$('div.ptogtitle',g.mDiv).click
					(
					 	function ()
							{
								$(g.gDiv).toggleClass('hideBody');
								$(this).toggleClass('vsble');
							}
					);
				}
			//g.rePosDrag();
		}

		//setup cdrops
		g.cdropleft = document.createElement('span');
		g.cdropleft.className = 'cdropleft';
		g.cdropright = document.createElement('span');
		g.cdropright.className = 'cdropright';

		//add block
		g.block.className = 'gBlock';
		var gh = $(g.bDiv).height();
		var gtop = g.bDiv.offsetTop;
		$(g.block).css(
		{
			width: g.bDiv.style.width,
			height: gh,
			background: 'white',
			position: 'relative',
			marginBottom: (gh * -1),
			zIndex: 1,
			top: gtop,
			left: '0px'
		}
		);
		$(g.block).fadeTo(0,p.blockOpacity);				
		
		// add column control
		if ($('th',g.hDiv).length)
		{
			
			g.nDiv.className = 'nDiv';
			g.nDiv.innerHTML = "<table cellpadding='0' cellspacing='0'><tbody></tbody></table>";
			$(g.nDiv).css(
			{
				marginBottom: (gh * -1),
				display: 'none',
				top: gtop
			}
			).noSelect()
			;
			
			var cn = 0;
			
			
			$('th div',g.hDiv).each
			(
			 	function ()
					{
						var kcol = $("th[axis='col" + cn + "']",g.hDiv)[0];
						var chk = 'checked="checked"';
						if (kcol.style.display=='none') chk = '';
						
						$('tbody',g.nDiv).append('<tr><td class="ndcol1"><input type="checkbox" '+ chk +' class="togCol" value="'+ cn +'" /></td><td class="ndcol2">'+this.innerHTML+'</td></tr>');
						cn++;
					}
			);
			
			if ($.browser.msie&&$.browser.version<7.0)
				$('tr',g.nDiv).hover
				(
				 	function () {$(this).addClass('ndcolover');},
					function () {$(this).removeClass('ndcolover');}
				);
			
			$('td.ndcol2',g.nDiv).click
			(
			 	function ()
					{
						if ($('input:checked',g.nDiv).length<=p.minColToggle&&$(this).prev().find('input')[0].checked) return false;
						return g.toggleCol($(this).prev().find('input').val());
					}
			);
			
			$('input.togCol',g.nDiv).click
			(
			 	function ()
					{
						
						if ($('input:checked',g.nDiv).length<p.minColToggle&&this.checked==false) return false;
						$(this).parent().next().trigger('click');
						//return false;
					}
			);


			$(g.gDiv).prepend(g.nDiv);
			
			$(g.nBtn).addClass('nBtn')
			.html('<div></div>')
			.attr('title','Hide/Show Columns')
			.click
			(
			 	function ()
				{
			 	$(g.nDiv).toggle(); return true;
				}
			);
			
			if (p.showToggleBtn) $(g.gDiv).prepend(g.nBtn);
			
		}
		
		// add date edit layer
		$(g.iDiv)
		.addClass('iDiv')
		.css({display:'none'})
		;
		$(g.bDiv).append(g.iDiv);
		
		// add flexigrid events
		$(g.bDiv)
		.hover(function(){$(g.nDiv).hide();$(g.nBtn).hide();},function(){if (g.multisel) g.multisel = false;})
		;
		$(g.gDiv)
		.hover(function(){},function(){$(g.nDiv).hide();$(g.nBtn).hide();})
		;
		
		//add document events
		$(document)
		.mousemove(function(e){g.dragMove(e)})
		.mouseup(function(e){g.dragEnd()})
		.hover(function(){},function (){g.dragEnd()})
		;
		
		//browser adjustments
		if ($.browser.msie&&$.browser.version<7.0)
		{
			$('.hDiv,.bDiv,.mDiv,.pDiv,.vGrip,.tDiv, .sDiv',g.gDiv)
			.css({width: '100%'});
			$(g.gDiv).addClass('ie6');
			if (p.width!='auto') $(g.gDiv).addClass('ie6fullwidthbug');			
		} 
		
		g.rePosDrag();
		g.fixHeight();
		
		//make grid functions accessible
		t.p = p;
		t.grid = g;
		
		// load data
		if (p.url&&p.autoload) 
			{
			g.populate();
			}
		
		return t;		
			
	};

	var docloaded = false;

	$(document).ready(function () {docloaded = true} );

	$.fn.flexigrid = function(p) {

		return this.each( function() {
				if (!docloaded)
				{
					$(this).hide();
					var t = this;
					$(document).ready
					(
						function ()
						{
						$.addFlex(t,p);
						}
					);
				} else {
					$.addFlex(this,p);
				}
			});

	}; //end flexigrid

	$.fn.flexReload = function(p) { // function to reload grid

		return this.each( function() {
				if (this.grid&&this.p.url) this.grid.populate();
			});

	}; //end flexReload

	$.fn.flexOptions = function(p) { //function to update general options

		return this.each( function() {
				if (this.grid) $.extend(this.p,p);
			});

	}; //end flexOptions

	$.fn.flexToggleCol = function(cid,visible) { // function to reload grid

		return this.each( function() {
				if (this.grid) this.grid.toggleCol(cid,visible);
			});

	}; //end flexToggleCol

	$.fn.flexAddData = function(data) { // function to add data to grid

		return this.each( function() {
				if (this.grid) this.grid.addData(data);
			});

	};

	$.fn.noSelect = function(p) { //no select plugin by me :-)

		if (p == null) 
			prevent = true;
		else
			prevent = p;

		if (prevent) {
		
		return this.each(function ()
			{
				if ($.browser.msie||$.browser.safari) $(this).bind('selectstart',function(){return false;});
				else if ($.browser.mozilla) 
					{
						$(this).css('MozUserSelect','none');
						$('body').trigger('focus');
					}
				else if ($.browser.opera) $(this).bind('mousedown',function(){return false;});
				else $(this).attr('unselectable','on');
			});
			
		} else {

		
		return this.each(function ()
			{
				if ($.browser.msie||$.browser.safari) $(this).unbind('selectstart');
				else if ($.browser.mozilla) $(this).css('MozUserSelect','inherit');
				else if ($.browser.opera) $(this).unbind('mousedown');
				else $(this).removeAttr('unselectable','on');
			});
		
		}

	}; //end noSelect
		
})(jQuery);;/*
 * Autocomplete - jQuery plugin 1.0.2
 *
 * Copyright (c) 2007 Dylan Verheul, Dan G. Switzer, Anjesh Tuladhar, Jörn Zaefferer
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Revision: $Id: jquery.autocomplete.js 5747 2008-06-25 18:30:55Z joern.zaefferer $
 *
 */

;(function($) {
	
$.fn.extend({
	autocomplete: function(urlOrData, options) {
		var isUrl = typeof urlOrData == "string";
		options = $.extend({}, $.Autocompleter.defaults, {
			url: isUrl ? urlOrData : null,
			data: isUrl ? null : urlOrData,
			delay: isUrl ? $.Autocompleter.defaults.delay : 10,
			max: options && !options.scroll ? 10 : 150
		}, options);
		
		// if highlight is set to false, replace it with a do-nothing function
		options.highlight = options.highlight || function(value) { return value; };
		
		// if the formatMatch option is not specified, then use formatItem for backwards compatibility
		options.formatMatch = options.formatMatch || options.formatItem;
		
		return this.each(function() {
			new $.Autocompleter(this, options);
		});
	},
	result: function(handler) {
		return this.bind("result", handler);
	},
	search: function(handler) {
		return this.trigger("search", [handler]);
	},
	flushCache: function() {
		return this.trigger("flushCache");
	},
	setOptions: function(options){
		return this.trigger("setOptions", [options]);
	},
	unautocomplete: function() {
		return this.trigger("unautocomplete");
	}
});

$.Autocompleter = function(input, options) {

	var KEY = {
		UP: 38,
		DOWN: 40,
		DEL: 46,
		TAB: 9,
		RETURN: 13,
		ESC: 27,
		COMMA: 188,
		PAGEUP: 33,
		PAGEDOWN: 34,
		BACKSPACE: 8
	};

	// Create $ object for input element
	var $input = $(input).attr("autocomplete", "off").addClass(options.inputClass);

	var timeout;
	var previousValue = "";
	var cache = $.Autocompleter.Cache(options);
	var hasFocus = 0;
	var lastKeyPressCode;
	var config = {
		mouseDownOnSelect: false
	};
	var select = $.Autocompleter.Select(options, input, selectCurrent, config);
	
	var blockSubmit;
	
	// prevent form submit in opera when selecting with return key
	$.browser.opera && $(input.form).bind("submit.autocomplete", function() {
		if (blockSubmit) {
			blockSubmit = false;
			return false;
		}
	});
	
	// only opera doesn't trigger keydown multiple times while pressed, others don't work with keypress at all
	$input.bind(($.browser.opera ? "keypress" : "keydown") + ".autocomplete", function(event) {
		// track last key pressed
		lastKeyPressCode = event.keyCode;
		switch(event.keyCode) {
		
			case KEY.UP:
				event.preventDefault();
				if ( select.visible() ) {
					select.prev();
				} else {
					onChange(0, true);
				}
				break;
				
			case KEY.DOWN:
				event.preventDefault();
				if ( select.visible() ) {
					select.next();
				} else {
					onChange(0, true);
				}
				break;
				
			case KEY.PAGEUP:
				event.preventDefault();
				if ( select.visible() ) {
					select.pageUp();
				} else {
					onChange(0, true);
				}
				break;
				
			case KEY.PAGEDOWN:
				event.preventDefault();
				if ( select.visible() ) {
					select.pageDown();
				} else {
					onChange(0, true);
				}
				break;
			
			// matches also semicolon
			case options.multiple && $.trim(options.multipleSeparator) == "," && KEY.COMMA:
			case KEY.TAB:
			case KEY.RETURN:
				if( selectCurrent() ) {
					// stop default to prevent a form submit, Opera needs special handling
					event.preventDefault();
					blockSubmit = true;
					return false;
				}
				break;
				
			case KEY.ESC:
				select.hide();
				break;
				
			default:
				clearTimeout(timeout);
				timeout = setTimeout(onChange, options.delay);
				break;
		}
	}).focus(function(){
		// track whether the field has focus, we shouldn't process any
		// results if the field no longer has focus
		hasFocus++;
	}).blur(function() {
		hasFocus = 0;
		if (!config.mouseDownOnSelect) {
			hideResults();
		}
	}).click(function() {
		// show select when clicking in a focused field
		if ( hasFocus++ > 1 && !select.visible() ) {
			onChange(0, true);
		}
	}).bind("search", function() {
		// TODO why not just specifying both arguments?
		var fn = (arguments.length > 1) ? arguments[1] : null;
		function findValueCallback(q, data) {
			var result;
			if( data && data.length ) {
				for (var i=0; i < data.length; i++) {
					if( data[i].result.toLowerCase() == q.toLowerCase() ) {
						result = data[i];
						break;
					}
				}
			}
			if( typeof fn == "function" ) fn(result);
			else $input.trigger("result", result && [result.data, result.value]);
		}
		$.each(trimWords($input.val()), function(i, value) {
			request(value, findValueCallback, findValueCallback);
		});
	}).bind("flushCache", function() {
		cache.flush();
	}).bind("setOptions", function() {
		$.extend(options, arguments[1]);
		// if we've updated the data, repopulate
		if ( "data" in arguments[1] )
			cache.populate();
	}).bind("unautocomplete", function() {
		select.unbind();
		$input.unbind();
		$(input.form).unbind(".autocomplete");
	});
	
	
	function selectCurrent() {
		var selected = select.selected();
		if( !selected )
			return false;
		
		var v = selected.result;
		previousValue = v;
		
		if ( options.multiple ) {
			var words = trimWords($input.val());
			if ( words.length > 1 ) {
				v = words.slice(0, words.length - 1).join( options.multipleSeparator ) + options.multipleSeparator + v;
			}
			v += options.multipleSeparator;
		}
		
		$input.val(v);
		hideResultsNow();
		$input.trigger("result", [selected.data, selected.value]);
		return true;
	}
	
	function onChange(crap, skipPrevCheck) {
		if( lastKeyPressCode == KEY.DEL ) {
			select.hide();
			return;
		}
		
		var currentValue = $input.val();
		
		if ( !skipPrevCheck && currentValue == previousValue )
			return;
		
		previousValue = currentValue;
		
		currentValue = lastWord(currentValue);
		if ( currentValue.length >= options.minChars) {
			$input.addClass(options.loadingClass);
			if (!options.matchCase)
				currentValue = currentValue.toLowerCase();
			request(currentValue, receiveData, hideResultsNow);
		} else {
			stopLoading();
			select.hide();
		}
	};
	
	function trimWords(value) {
		if ( !value ) {
			return [""];
		}
		var words = value.split( options.multipleSeparator );
		var result = [];
		$.each(words, function(i, value) {
			if ( $.trim(value) )
				result[i] = $.trim(value);
		});
		return result;
	}
	
	function lastWord(value) {
		if ( !options.multiple )
			return value;
		var words = trimWords(value);
		return words[words.length - 1];
	}
	
	// fills in the input box w/the first match (assumed to be the best match)
	// q: the term entered
	// sValue: the first matching result
	function autoFill(q, sValue){
		// autofill in the complete box w/the first match as long as the user hasn't entered in more data
		// if the last user key pressed was backspace, don't autofill
		if( options.autoFill && (lastWord($input.val()).toLowerCase() == q.toLowerCase()) && lastKeyPressCode != KEY.BACKSPACE ) {
			// fill in the value (keep the case the user has typed)
			$input.val($input.val() + sValue.substring(lastWord(previousValue).length));
			// select the portion of the value not typed by the user (so the next character will erase)
			$.Autocompleter.Selection(input, previousValue.length, previousValue.length + sValue.length);
		}
	};

	function hideResults() {
		clearTimeout(timeout);
		timeout = setTimeout(hideResultsNow, 200);
	};

	function hideResultsNow() {
		var wasVisible = select.visible();
		select.hide();
		clearTimeout(timeout);
		stopLoading();
		if (options.mustMatch) {
			// call search and run callback
			$input.search(
				function (result){
					// if no value found, clear the input box
					if( !result ) {
						if (options.multiple) {
							var words = trimWords($input.val()).slice(0, -1);
							$input.val( words.join(options.multipleSeparator) + (words.length ? options.multipleSeparator : "") );
						}
						else
							$input.val( "" );
					}
				}
			);
		}
		if (wasVisible)
			// position cursor at end of input field
			$.Autocompleter.Selection(input, input.value.length, input.value.length);
	};

	function receiveData(q, data) {
		if ( data && data.length && hasFocus ) {
			stopLoading();
			select.display(data, q);
			autoFill(q, data[0].value);
			select.show();
		} else {
			hideResultsNow();
		}
	};

	function request(term, success, failure) {
		if (!options.matchCase)
			term = term.toLowerCase();
		var data = cache.load(term);
		// recieve the cached data
		if (data && data.length) {
			success(term, data);
		// if an AJAX url has been supplied, try loading the data now
		} else if( (typeof options.url == "string") && (options.url.length > 0) ){
			
			var extraParams = {
				timestamp: +new Date()
			};
			$.each(options.extraParams, function(key, param) {
				extraParams[key] = typeof param == "function" ? param() : param;
			});
			
			$.ajax({
				// try to leverage ajaxQueue plugin to abort previous requests
				mode: "abort",
				// limit abortion to this input
				port: "autocomplete" + input.name,
				dataType: options.dataType,
				url: options.url,
				data: $.extend({
					s: lastWord(term),
					limit: options.max
				}, extraParams),
				success: function(data) {
					var parsed = options.parse && options.parse(data) || parse(data);
					cache.add(term, parsed);
					success(term, parsed);
				}
			});
		} else {
			// if we have a failure, we need to empty the list -- this prevents the the [TAB] key from selecting the last successful match
			select.emptyList();
			failure(term);
		}
	};
	
	function parse(data) {
		var parsed = [];
		var rows = data.split("\n");
		for (var i=0; i < rows.length; i++) {
			var row = $.trim(rows[i]);
			if (row) {
				row = row.split("|");
				parsed[parsed.length] = {
					data: row,
					value: row[0],
					result: options.formatResult && options.formatResult(row, row[0]) || row[0]
				};
			}
		}
		return parsed;
	};

	function stopLoading() {
		$input.removeClass(options.loadingClass);
	};

};

$.Autocompleter.defaults = {
	inputClass: "ac_input",
	resultsClass: "ac_results",
	loadingClass: "ac_loading",
	minChars: 0,
	delay: 400,
	matchCase: false,
	matchSubset: true,
	matchContains: false,
	cacheLength: 10,
	max: 100,
	mustMatch: false,
	extraParams: {},
	selectFirst: true,
	formatItem: function(row) { return row[0]; },
	formatMatch: null,
	autoFill: false,
	width: 0,
	multiple: false,
	multipleSeparator: ", ",
	highlight: function(value, term) {
		return value.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + term.replace(/([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1") + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>");
	},
    scroll: true,
    scrollHeight: 180
};

$.Autocompleter.Cache = function(options) {

	var data = {};
	var length = 0;
	
	function matchSubset(s, sub) {
		if (!options.matchCase) 
			s = s.toLowerCase();
		var i = s.indexOf(sub);
		if (i == -1) return false;
		return i == 0 || options.matchContains;
	};
	
	function add(q, value) {
		if (length > options.cacheLength){
			flush();
		}
		if (!data[q]){ 
			length++;
		}
		data[q] = value;
	}
	
	function populate(){
		if( !options.data ) return false;
		// track the matches
		var stMatchSets = {},
			nullData = 0;

		// no url was specified, we need to adjust the cache length to make sure it fits the local data store
		if( !options.url ) options.cacheLength = 1;
		
		// track all options for minChars = 0
		stMatchSets[""] = [];
		
		// loop through the array and create a lookup structure
		for ( var i = 0, ol = options.data.length; i < ol; i++ ) {
			var rawValue = options.data[i];
			// if rawValue is a string, make an array otherwise just reference the array
			rawValue = (typeof rawValue == "string") ? [rawValue] : rawValue;
			
			var value = options.formatMatch(rawValue, i+1, options.data.length);
			if ( value === false )
				continue;
				
			var firstChar = value.charAt(0).toLowerCase();
			// if no lookup array for this character exists, look it up now
			if( !stMatchSets[firstChar] ) 
				stMatchSets[firstChar] = [];

			// if the match is a string
			var row = {
				value: value,
				data: rawValue,
				result: options.formatResult && options.formatResult(rawValue) || value
			};
			
			// push the current match into the set list
			stMatchSets[firstChar].push(row);

			// keep track of minChars zero items
			if ( nullData++ < options.max ) {
				stMatchSets[""].push(row);
			}
		};

		// add the data items to the cache
		$.each(stMatchSets, function(i, value) {
			// increase the cache size
			options.cacheLength++;
			// add to the cache
			add(i, value);
		});
	}
	
	// populate any existing data
	setTimeout(populate, 25);
	
	function flush(){
		data = {};
		length = 0;
	}
	
	return {
		flush: flush,
		add: add,
		populate: populate,
		load: function(q) {
			if (!options.cacheLength || !length)
				return null;
			/* 
			 * if dealing w/local data and matchContains than we must make sure
			 * to loop through all the data collections looking for matches
			 */
			if( !options.url && options.matchContains ){
				// track all matches
				var csub = [];
				// loop through all the data grids for matches
				for( var k in data ){
					// don't search through the stMatchSets[""] (minChars: 0) cache
					// this prevents duplicates
					if( k.length > 0 ){
						var c = data[k];
						$.each(c, function(i, x) {
							// if we've got a match, add it to the array
							if (matchSubset(x.value, q)) {
								csub.push(x);
							}
						});
					}
				}				
				return csub;
			} else 
			// if the exact item exists, use it
			if (data[q]){
				return data[q];
			} else
			if (options.matchSubset) {
				for (var i = q.length - 1; i >= options.minChars; i--) {
					var c = data[q.substr(0, i)];
					if (c) {
						var csub = [];
						$.each(c, function(i, x) {
							if (matchSubset(x.value, q)) {
								csub[csub.length] = x;
							}
						});
						return csub;
					}
				}
			}
			return null;
		}
	};
};

$.Autocompleter.Select = function (options, input, select, config) {
	var CLASSES = {
		ACTIVE: "ac_over"
	};
	
	var listItems,
		active = -1,
		data,
		term = "",
		needsInit = true,
		element,
		list;
	
	// Create results
	function init() {
		if (!needsInit)
			return;
		element = $("<div/>")
		.hide()
		.addClass(options.resultsClass)
		.css("position", "absolute")
		.appendTo(document.body);
		 
		 $("<div/>")
		.addClass('ac_results-inner')
		.appendTo(element);
	
		list = $("<ul/>").appendTo('.ac_results-inner').mouseover( function(event) {
			if(target(event).nodeName && target(event).nodeName.toUpperCase() == 'LI') {
	            active = $("li", list).removeClass(CLASSES.ACTIVE).index(target(event));
			    $(target(event)).addClass(CLASSES.ACTIVE);            
	        }
		}).click(function(event) {
			$(target(event)).addClass(CLASSES.ACTIVE);
			select();
			// TODO provide option to avoid setting focus again after selection? useful for cleanup-on-focus
			input.focus();
			return false;
		}).mousedown(function() {
			config.mouseDownOnSelect = true;
		}).mouseup(function() {
			config.mouseDownOnSelect = false;
		});
		
		if( options.width > 0 )
			element.css("width", options.width);
			
		needsInit = false;
	} 
	
	function target(event) {
		var element = event.target;
		while(element && element.tagName != "LI")
			element = element.parentNode;
		// more fun with IE, sometimes event.target is empty, just ignore it then
		if(!element)
			return [];
		return element;
	}

	function moveSelect(step) {
		listItems.slice(active, active + 1).removeClass(CLASSES.ACTIVE);
		movePosition(step);
        var activeItem = listItems.slice(active, active + 1).addClass(CLASSES.ACTIVE);
        if(options.scroll) {
            var offset = 0;
            listItems.slice(0, active).each(function() {
				offset += this.offsetHeight;
			});
            if((offset + activeItem[0].offsetHeight - list.scrollTop()) > list[0].clientHeight) {
                list.scrollTop(offset + activeItem[0].offsetHeight - list.innerHeight());
            } else if(offset < list.scrollTop()) {
                list.scrollTop(offset);
            }
        }
	};
	
	function movePosition(step) {
		active += step;
		if (active < 0) {
			active = listItems.size() - 1;
		} else if (active >= listItems.size()) {
			active = 0;
		}
	}
	
	function limitNumberOfItems(available) {
		return options.max && options.max < available
			? options.max
			: available;
	}
	
	function fillList() {
		list.empty();
		var max = limitNumberOfItems(data.length);
		for (var i=0; i < max; i++) {
			if (!data[i])
				continue;
			var formatted = options.formatItem(data[i].data, i+1, max, data[i].value, term);
			if ( formatted === false )
				continue;
			var li = $("<li/>").html( options.highlight(formatted, term) ).addClass(i%2 == 0 ? "ac_even" : "ac_odd").appendTo(list)[0];
			$.data(li, "ac_data", data[i]);
		}
		listItems = list.find("li");
		if ( options.selectFirst ) {
			listItems.slice(0, 1).addClass(CLASSES.ACTIVE);
			active = 0;
		}
		// apply bgiframe if available
		if ( $.fn.bgiframe )
			list.bgiframe();
	}
	
	return {
		display: function(d, q) {
			init();
			data = d;
			term = q;
			fillList();
		},
		next: function() {
			moveSelect(1);
		},
		prev: function() {
			moveSelect(-1);
		},
		pageUp: function() {
			if (active != 0 && active - 8 < 0) {
				moveSelect( -active );
			} else {
				moveSelect(-8);
			}
		},
		pageDown: function() {
			if (active != listItems.size() - 1 && active + 8 > listItems.size()) {
				moveSelect( listItems.size() - 1 - active );
			} else {
				moveSelect(8);
			}
		},
		hide: function() {
			element && element.hide();
			listItems && listItems.removeClass(CLASSES.ACTIVE);
			active = -1;
		},
		visible : function() {
			return element && element.is(":visible");
		},
		current: function() {
			return this.visible() && (listItems.filter("." + CLASSES.ACTIVE)[0] || options.selectFirst && listItems[0]);
		},
		show: function() {
			var offset = $(input).offset();
			element.css({
				width: typeof options.width == "string" || options.width > 0 ? options.width : $(input).width(),
				top: offset.top + input.offsetHeight,
				left: offset.left
			}).show();
            if(options.scroll) {
                list.scrollTop(0);
                list.css({
					maxHeight: options.scrollHeight,
					overflow: 'auto'
				});
				
                if($.browser.msie && typeof document.body.style.maxHeight === "undefined") {
					var listHeight = 0;
					listItems.each(function() {
						listHeight += this.offsetHeight;
					});
					var scrollbarsVisible = listHeight > options.scrollHeight;
                    list.css('height', scrollbarsVisible ? options.scrollHeight : listHeight );
					if (!scrollbarsVisible) {
						// IE doesn't recalculate width when scrollbar disappears
						listItems.width( list.width() - parseInt(listItems.css("padding-left")) - parseInt(listItems.css("padding-right")) );
					}
                }
                
            }
		},
		selected: function() {
			var selected = listItems && listItems.filter("." + CLASSES.ACTIVE).removeClass(CLASSES.ACTIVE);
			return selected && selected.length && $.data(selected[0], "ac_data");
		},
		emptyList: function (){
			list && list.empty();
		},
		unbind: function() {
			element && element.remove();
		}
	};
};

$.Autocompleter.Selection = function(field, start, end) {
	if( field.createTextRange ){
		var selRange = field.createTextRange();
		selRange.collapse(true);
		selRange.moveStart("character", start);
		selRange.moveEnd("character", end);
		selRange.select();
	} else if( field.setSelectionRange ){
		field.setSelectionRange(start, end);
	} else {
		if( field.selectionStart ){
			field.selectionStart = start;
			field.selectionEnd = end;
		}
	}
	field.focus();
};

})(jQuery);;/*
 * jsTree 0.9.8
 * http://jstree.com/
 *
 * Copyright (c) 2009 Ivan Bozhanov (vakata.com)
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Date: 2009-05-15
 *
 */
(function($){$.fn.tree=function(opts){return this.each(function(){var conf=$.extend({},opts);if(tree_component.inst&&tree_component.inst[$(this).attr('id')])tree_component.inst[$(this).attr('id')].destroy();if(conf!==false)new tree_component().init(this,conf)})};$.tree_create=function(){return new tree_component()};$.tree_focused=function(){return tree_component.inst[tree_component.focused]};$.tree_reference=function(id){return tree_component.inst[id]||null};$.tree_rollback=function(data){for(var i in data){if(typeof data[i]=="function")continue;var tmp=tree_component.inst[i];var lock=!tmp.locked;if(lock)tmp.lock(true);if(tmp.inp)tmp.inp.val("").blur();tmp.context.append=false;tmp.container.html(data[i].html).find(".dragged").removeClass("dragged").end().find("div.context").remove();if(data[i].selected){tmp.selected=$("#"+data[i].selected);tmp.selected_arr=[];tmp.container.find("a.clicked").each(function(){tmp.selected_arr.push(tmp.get_node(this))})}if(lock)tmp.lock(false);delete lock;delete tmp}};function tree_component(){if(typeof tree_component.inst=="undefined"){tree_component.cntr=0;tree_component.inst={};tree_component.drag_drop={isdown:false,drag_node:false,drag_help:false,init_x:false,init_y:false,moving:false,origin_tree:false,marker:false,move_type:false,ref_node:false,appended:false,foreign:false,droppable:[],open_time:false,scroll_time:false};tree_component.mousedown=function(event){var tmp=$(event.target);if(tree_component.drag_drop.droppable.length&&tmp.is("."+tree_component.drag_drop.droppable.join(", ."))){tree_component.drag_drop.drag_help=$("<div id='jstree-dragged' class='tree tree-default'><ul><li class='last dragged foreign "+event.target.className+"'><a href='#'>"+tmp.text()+"</a></li></ul></div>");tree_component.drag_drop.drag_node=tree_component.drag_drop.drag_help.find("li:eq(0)");tree_component.drag_drop.isdown=true;tree_component.drag_drop.foreign=tmp;tmp.blur();event.preventDefault();event.stopPropagation();return false}event.stopPropagation();return true};tree_component.mouseup=function(event){var tmp=tree_component.drag_drop;if(tmp.open_time)clearTimeout(tmp.open_time);if(tmp.scroll_time)clearTimeout(tmp.scroll_time);if(tmp.foreign===false&&tmp.drag_node&&tmp.drag_node.size()){tmp.drag_help.remove();if(tmp.move_type){var tree1=tree_component.inst[tmp.ref_node.parents(".tree:eq(0)").attr("id")];if(tree1)tree1.moved(tmp.origin_tree.container.find("li.dragged"),tmp.ref_node,tmp.move_type,false,(tmp.origin_tree.settings.rules.drag_copy=="on"||(tmp.origin_tree.settings.rules.drag_copy=="ctrl"&&event.ctrlKey)))}tmp.move_type=false;tmp.ref_node=false}if(tmp.drag_node&&tmp.foreign!==false){tmp.drag_help.remove();if(tmp.move_type){var tree1=tree_component.inst[tmp.ref_node.parents(".tree:eq(0)").attr("id")];if(tree1)tree1.settings.callback.ondrop.call(null,tmp.foreign.get(0),tree1.get_node(tmp.ref_node).get(0),tmp.move_type,tree1)}tmp.foreign=false;tmp.move_type=false;tmp.ref_node=false}tree_component.drag_drop.marker.hide();tmp.drag_help=false;tmp.drag_node=false;tmp.isdown=false;tmp.init_x=false;tmp.init_y=false;tmp.moving=false;tmp.appended=false;$("li.dragged").removeClass("dragged");tmp.origin_tree=false;event.preventDefault();event.stopPropagation();return false};tree_component.mousemove=function(event){var tmp=tree_component.drag_drop;if(tmp.isdown){if(!tmp.moving&&Math.abs(tmp.init_x-event.pageX)<5&&Math.abs(tmp.init_y-event.pageY)<5){event.preventDefault();event.stopPropagation();return false}else tree_component.drag_drop.moving=true;if(tmp.open_time)clearTimeout(tmp.open_time);if(!tmp.appended){if(tmp.foreign!==false)tmp.origin_tree=$.tree_focused();$("body").append(tmp.drag_help);tmp.w=tmp.drag_help.width();tmp.appended=true}tmp.drag_help.css({"left":(event.pageX-(tmp.origin_tree.settings.ui.rtl?tmp.w:-5)),"top":(event.pageY+15)});if(event.target.tagName=="IMG"&&event.target.id=="marker")return false;var et=$(event.target);var cnt=et.is(".tree")?et:et.parents(".tree:eq(0)");if(cnt.size()==0||!tree_component.inst[cnt.attr("id")]){if(tmp.scroll_time)clearTimeout(tmp.scroll_time);if(tmp.drag_help.find("IMG").size()==0){tmp.drag_help.find("li:eq(0)").append("<img style='position:absolute; "+(tmp.origin_tree.settings.ui.rtl?"right":"left")+":4px; top:0px; background:white; padding:2px;' src='"+tmp.origin_tree.settings.ui.theme_path+"remove.png' />")}tmp.move_type=false;tmp.ref_node=false;tree_component.drag_drop.marker.hide();return false}var tree2=tree_component.inst[cnt.attr("id")];tree2.off_height();if(tmp.foreign===false&&tmp.origin_tree.container.get(0)!=tree2.container.get(0)&&(!tmp.origin_tree.settings.rules.multitree||!tree2.settings.rules.multitree)){if(tmp.drag_help.find("IMG").size()==0){tmp.drag_help.find("li:eq(0)").append("<img style='position:absolute; "+(tmp.origin_tree.settings.ui.rtl?"right":"left")+":4px; top:0px; background:white; padding:2px;' src='"+tmp.origin_tree.settings.ui.theme_path+"remove.png' />")}tmp.move_type=false;tmp.ref_node=false;tree_component.drag_drop.marker.hide();return false}if(tmp.scroll_time)clearTimeout(tmp.scroll_time);tmp.scroll_time=setTimeout(function(){tree2.scrollCheck(event.pageX,event.pageY)},50);var mov=false;var st=cnt.scrollTop();if(event.target.tagName=="A"){if(et.is("#jstree-dragged"))return false;if(tree2.get_node(event.target).hasClass("closed")){tmp.open_time=setTimeout(function(){tree2.open_branch(et)},500)}var et_off=et.offset();var goTo={x:(et_off.left-1),y:(event.pageY-et_off.top)};if(cnt.children("ul:eq(0)").hasClass("rtl"))goTo.x+=et.width()-8;var arr=[];if(goTo.y<tree2.li_height/3+1)arr=["before","inside","after"];else if(goTo.y>tree2.li_height*2/3-1)arr=["after","inside","before"];else{if(goTo.y<tree2.li_height/2)arr=["inside","before","after"];else arr=["inside","after","before"]}var ok=false;$.each(arr,function(i,val){if(tree2.checkMove(tmp.origin_tree.container.find("li.dragged"),et,val)){mov=val;ok=true;return false}});if(ok){switch(mov){case"before":goTo.y=et_off.top-2;if(cnt.children("ul:eq(0)").hasClass("rtl")){tree_component.drag_drop.marker.attr("src",tree2.settings.ui.theme_path+"marker_rtl.gif").width(40)}else{tree_component.drag_drop.marker.attr("src",tree2.settings.ui.theme_path+"marker.gif").width(40)}break;case"after":goTo.y=et_off.top-2+tree2.li_height;if(cnt.children("ul:eq(0)").hasClass("rtl")){tree_component.drag_drop.marker.attr("src",tree2.settings.ui.theme_path+"marker_rtl.gif").width(40)}else{tree_component.drag_drop.marker.attr("src",tree2.settings.ui.theme_path+"marker.gif").width(40)}break;case"inside":goTo.x-=2;if(cnt.children("ul:eq(0)").hasClass("rtl")){goTo.x+=36}goTo.y=et_off.top-2+tree2.li_height/2;tree_component.drag_drop.marker.attr("src",tree2.settings.ui.theme_path+"plus.gif").width(11);break}tmp.move_type=mov;tmp.ref_node=$(event.target);tmp.drag_help.find("IMG").remove();tree_component.drag_drop.marker.css({"left":goTo.x,"top":goTo.y}).show()}}if((et.is(".tree")||et.is("ul"))&&et.find("li:eq(0)").size()==0){var et_off=et.offset();tmp.move_type="inside";tmp.ref_node=cnt.children("ul:eq(0)");tmp.drag_help.find("IMG").remove();tree_component.drag_drop.marker.attr("src",tree2.settings.ui.theme_path+"plus.gif").width(11);tree_component.drag_drop.marker.css({"left":et_off.left+(cnt.children("ul:eq(0)").hasClass("rtl")?(cnt.width()-10):10),"top":et_off.top+15}).show()}else if(event.target.tagName!="A"||!ok){if(tmp.drag_help.find("IMG").size()==0){tmp.drag_help.find("li:eq(0)").append("<img style='position:absolute; "+(tmp.origin_tree.settings.ui.rtl?"right":"left")+":4px; top:0px; background:white; padding:2px;' src='"+tmp.origin_tree.settings.ui.theme_path+"remove.png' />")}tmp.move_type=false;tmp.ref_node=false;tree_component.drag_drop.marker.hide()}event.preventDefault();event.stopPropagation();return false}return true}};return{cntr:++tree_component.cntr,settings:{data:{type:"predefined",method:"GET",async:false,async_data:function(NODE,TREE_OBJ){return{id:$(NODE).attr("id")||0}},url:false,json:false,xml:false},selected:false,opened:[],languages:[],path:false,cookies:false,ui:{dots:true,rtl:false,animation:0,hover_mode:true,scroll_spd:4,theme_path:false,theme_name:"default",context:[{id:"create",label:"Create",icon:"create.png",visible:function(NODE,TREE_OBJ){if(NODE.length!=1)return false;return TREE_OBJ.check("creatable",NODE)},action:function(NODE,TREE_OBJ){TREE_OBJ.create(false,TREE_OBJ.get_node(NODE[0]))}},"separator",{id:"rename",label:"Rename",icon:"rename.png",visible:function(NODE,TREE_OBJ){if(NODE.length!=1)return false;return TREE_OBJ.check("renameable",NODE)},action:function(NODE,TREE_OBJ){TREE_OBJ.rename(NODE)}},{id:"delete",label:"Delete",icon:"remove.png",visible:function(NODE,TREE_OBJ){var ok=true;$.each(NODE,function(){if(TREE_OBJ.check("deletable",this)==false)ok=false;return false});return ok},action:function(NODE,TREE_OBJ){$.each(NODE,function(){TREE_OBJ.remove(this)})}}]},rules:{multiple:false,metadata:false,type_attr:"rel",multitree:false,createat:"bottom",use_inline:false,clickable:"all",renameable:"all",deletable:"all",creatable:"all",draggable:"none",dragrules:"all",drag_copy:false,droppable:[],drag_button:"left"},lang:{new_node:"New folder",loading:"Loading ..."},callback:{beforechange:function(NODE,TREE_OBJ){return true},beforeopen:function(NODE,TREE_OBJ){return true},beforeclose:function(NODE,TREE_OBJ){return true},beforemove:function(NODE,REF_NODE,TYPE,TREE_OBJ){return true},beforecreate:function(NODE,REF_NODE,TYPE,TREE_OBJ){return true},beforerename:function(NODE,LANG,TREE_OBJ){return true},beforedelete:function(NODE,TREE_OBJ){return true},onJSONdata:function(DATA,TREE_OBJ){return DATA},onselect:function(NODE,TREE_OBJ){},ondeselect:function(NODE,TREE_OBJ){},onchange:function(NODE,TREE_OBJ){},onrename:function(NODE,LANG,TREE_OBJ,RB){},onmove:function(NODE,REF_NODE,TYPE,TREE_OBJ,RB){},oncopy:function(NODE,REF_NODE,TYPE,TREE_OBJ,RB){},oncreate:function(NODE,REF_NODE,TYPE,TREE_OBJ,RB){},ondelete:function(NODE,TREE_OBJ,RB){},onopen:function(NODE,TREE_OBJ){},onopen_all:function(TREE_OBJ){},onclose:function(NODE,TREE_OBJ){},error:function(TEXT,TREE_OBJ){},ondblclk:function(NODE,TREE_OBJ){TREE_OBJ.toggle_branch.call(TREE_OBJ,NODE);TREE_OBJ.select_branch.call(TREE_OBJ,NODE)},onrgtclk:function(NODE,TREE_OBJ,EV){},onload:function(TREE_OBJ){},onfocus:function(TREE_OBJ){},ondrop:function(NODE,REF_NODE,TYPE,TREE_OBJ){}}},init:function(elem,conf){var _this=this;this.container=$(elem);if(this.container.size==0){alert("Invalid container node!");return}tree_component.inst[this.cntr]=this;if(!this.container.attr("id"))this.container.attr("id","jstree_"+this.cntr);tree_component.inst[this.container.attr("id")]=tree_component.inst[this.cntr];tree_component.focused=this.cntr;var opts=$.extend({},conf);if(opts&&opts.cookies){this.settings.cookies=$.extend({},this.settings.cookies,opts.cookies);delete opts.cookies;if(!this.settings.cookies.opts)this.settings.cookies.opts={}}if(opts&&opts.callback){this.settings.callback=$.extend({},this.settings.callback,opts.callback);delete opts.callback}if(opts&&opts.data){this.settings.data=$.extend({},this.settings.data,opts.data);delete opts.data}if(opts&&opts.ui){this.settings.ui=$.extend({},this.settings.ui,opts.ui);delete opts.ui}if(opts&&opts.rules){this.settings.rules=$.extend({},this.settings.rules,opts.rules);delete opts.rules}if(opts&&opts.lang){this.settings.lang=$.extend({},this.settings.lang,opts.lang);delete opts.lang}this.settings=$.extend({},this.settings,opts);if(this.settings.path==false){this.path="";$("script").each(function(){if(this.src.toString().match(/tree_component.*?js$/)){_this.path=this.src.toString().replace(/tree_component.*?js$/,"")}})}else this.path=this.settings.path;this.current_lang=this.settings.languages&&this.settings.languages.length?this.settings.languages[0]:false;if(this.settings.languages&&this.settings.languages.length){this.sn=get_sheet_num("tree_component.css");if(this.sn===false&&document.styleSheets.length)this.sn=document.styleSheets.length;var st=false;var id=this.container.attr("id")?"#"+this.container.attr("id"):".tree";for(var ln=0;ln<this.settings.languages.length;ln++){st=add_css(id+" ."+this.settings.languages[ln],this.sn);if(st!==false){if(this.settings.languages[ln]==this.current_lang)st.style.display="";else st.style.display="none"}}}if(this.settings.rules.droppable.length){for(var i in this.settings.rules.droppable){if(typeof this.settings.rules.droppable[i]=="function")continue;tree_component.drag_drop.droppable.push(this.settings.rules.droppable[i])}tree_component.drag_drop.droppable=$.unique(tree_component.drag_drop.droppable)}if(this.settings.ui.theme_path===false)this.settings.ui.theme_path=this.path+"themes/";this.theme=this.settings.ui.theme_path;if(_this.settings.ui.theme_name){this.theme+=_this.settings.ui.theme_name+"/";if(_this.settings.ui.theme_name!="themeroller"&&!tree_component.def_style){add_sheet(_this.settings.ui.theme_path+"default/style.css");tree_component.def_style=true}add_sheet(_this.theme+"style.css")}this.container.addClass("tree");if(_this.settings.ui.theme_name!="themeroller")this.container.addClass("tree-default");if(this.settings.ui.theme_name&&this.settings.ui.theme_name!="default")this.container.addClass("tree-"+_this.settings.ui.theme_name);if(this.settings.ui.theme_name=="themeroller")this.container.addClass("ui-widget ui-widget-content");if(this.settings.rules.multiple)this.selected_arr=[];this.offset=false;this.context_menu();this.hovered=false;this.locked=false;if(this.settings.rules.draggable!="none"&&tree_component.drag_drop.marker===false){var _this=this;tree_component.drag_drop.marker=$("<img>").attr({id:"marker",src:_this.settings.ui.theme_path+"marker.gif"}).css({height:"5px",width:"40px",display:"block",position:"absolute",left:"30px",top:"30px",zIndex:"1000"}).hide().appendTo("body")}this.refresh();this.attachEvents();this.focus()},off_height:function(){if(this.offset===false){this.container.css({position:"relative"});this.offset=this.container.offset();var tmp=0;tmp=parseInt($.curCSS(this.container.get(0),"paddingTop",true),10);if(tmp)this.offset.top+=tmp;tmp=parseInt($.curCSS(this.container.get(0),"borderTopWidth",true),10);if(tmp)this.offset.top+=tmp;this.container.css({position:""})}if(!this.li_height){var tmp=this.container.find("ul li.closed, ul li.leaf").eq(0);this.li_height=tmp.height();if(tmp.children("ul:eq(0)").size())this.li_height-=tmp.children("ul:eq(0)").height();if(!this.li_height)this.li_height=18}},context_menu:function(){this.context=false;if(this.settings.ui.context!=false){var str='<div class="tree-context tree-default-context tree-'+this.settings.ui.theme_name+'-context">';for(var i in this.settings.ui.context){if(typeof this.settings.ui.context[i]=="function")continue;if(this.settings.ui.context[i]=="separator"){str+="<span class='separator'>&nbsp;</span>";continue}var icn="";if(this.settings.ui.context[i].icon)icn='background-image:url(\''+(this.settings.ui.context[i].icon.indexOf("/")==-1?this.theme+this.settings.ui.context[i].icon:this.settings.ui.context[i].icon)+'\');';str+='<a rel="'+this.settings.ui.context[i].id+'" href="#" style="'+icn+'">'+this.settings.ui.context[i].label+'</a>'}str+='</div>';this.context=$(str);this.context.hide();this.context.append=false}},refresh:function(obj){if(this.locked)return this.error("LOCKED");var _this=this;this.is_partial_refresh=obj?true:false;this.opened=Array();if(this.settings.cookies&&$.cookie(this.settings.cookies.prefix+'_open')){var str=$.cookie(this.settings.cookies.prefix+'_open');var tmp=str.split(",");$.each(tmp,function(){if(this.replace(/^#/,"").length>0){_this.opened.push("#"+this.replace(/^#/,""))}});this.settings.opened=false}else if(this.settings.opened!=false){$.each(this.settings.opened,function(i,item){if(this.replace(/^#/,"").length>0){_this.opened.push("#"+this.replace(/^#/,""))}});this.settings.opened=false}else{this.container.find("li.open").each(function(i){if(this.id){_this.opened.push("#"+this.id)}})}if(this.selected){this.settings.selected=Array();if(obj){$(obj).find("li:has(a.clicked)").each(function(){$this=$(this);if($this.attr("id"))_this.settings.selected.push("#"+$this.attr("id"))})}else{if(this.selected_arr){$.each(this.selected_arr,function(){if(this.attr("id"))_this.settings.selected.push("#"+this.attr("id"))})}else{if(this.selected.attr("id"))this.settings.selected.push("#"+this.selected.attr("id"))}}}else if(this.settings.cookies&&$.cookie(this.settings.cookies.prefix+'_selected')){this.settings.selected=Array();var str=$.cookie(this.settings.cookies.prefix+'_selected');var tmp=str.split(",");$.each(tmp,function(){if(this.replace(/^#/,"").length>0){_this.settings.selected.push("#"+this.replace(/^#/,""))}})}else if(this.settings.selected!==false){var tmp=Array();if((typeof this.settings.selected).toLowerCase()=="object"){$.each(this.settings.selected,function(){if(this.replace(/^#/,"").length>0)tmp.push("#"+this.replace(/^#/,""))})}else{if(this.settings.selected.replace(/^#/,"").length>0)tmp.push("#"+this.settings.selected.replace(/^#/,""))}this.settings.selected=tmp}if(obj&&this.settings.data.async){this.opened=Array();obj=this.get_node(obj);obj.find("li.open").each(function(i){_this.opened.push("#"+this.id)});if(obj.hasClass("open"))obj.removeClass("open").addClass("closed");if(obj.hasClass("leaf"))obj.removeClass("leaf");obj.children("ul:eq(0)").html("");return this.open_branch(obj,true,function(){_this.reselect.apply(_this)})}if(this.settings.data.type=="xml_flat"||this.settings.data.type=="xml_nested"){this.scrtop=this.container.get(0).scrollTop;var xsl=(this.settings.data.type=="xml_flat")?"flat.xsl":"nested.xsl";if(this.settings.data.xml)this.container.getTransform(this.path+xsl,this.settings.data.xml,{params:{theme_name:_this.settings.ui.theme_name,theme_path:_this.theme},meth:_this.settings.data.method,dat:_this.settings.data.async_data.apply(_this,[obj,_this]),callback:function(){_this.context_menu.apply(_this);_this.reselect.apply(_this)}});else this.container.getTransform(this.path+xsl,this.settings.data.url,{params:{theme_name:_this.settings.ui.theme_name,theme_path:_this.theme},meth:_this.settings.data.method,dat:_this.settings.data.async_data.apply(_this,[obj,_this]),callback:function(){_this.context_menu.apply(_this);_this.reselect.apply(_this)}});return}else if(this.settings.data.type=="json"){if(this.settings.data.json){var str="";if(this.settings.data.json.length){for(var i=0;i<this.settings.data.json.length;i++){str+=this.parseJSON(this.settings.data.json[i])}}else str=this.parseJSON(this.settings.data.json);this.container.html("<ul>"+str+"</ul>");this.container.find("li:last-child").addClass("last").end().find("li:has(ul)").not(".open").addClass("closed");this.container.find("li").not(".open").not(".closed").addClass("leaf");this.context_menu();this.reselect()}else{var _this=this;$.ajax({type:this.settings.data.method,url:this.settings.data.url,data:this.settings.data.async_data(false,this),dataType:"json",success:function(data){data=_this.settings.callback.onJSONdata.call(null,data,_this);var str="";if(data.length){for(var i=0;i<data.length;i++){str+=_this.parseJSON(data[i])}}else str=_this.parseJSON(data);_this.container.html("<ul>"+str+"</ul>");_this.container.find("li:last-child").addClass("last").end().find("li:has(ul)").not(".open").addClass("closed");_this.container.find("li").not(".open").not(".closed").addClass("leaf");_this.context_menu.apply(_this);_this.reselect.apply(_this)},error:function(xhttp,textStatus,errorThrown){_this.error(errorThrown+" "+textStatus)}})}}else{this.container.children("ul:eq(0)");this.container.find("li:last-child").addClass("last").end().find("li:has(ul)").not(".open").addClass("closed");this.container.find("li").not(".open").not(".closed").addClass("leaf");this.reselect()}},parseJSON:function(data){if(!data||!data.data)return"";var str="";str+="<li ";var cls=false;if(data.attributes){for(var i in data.attributes){if(typeof data.attributes[i]=="function")continue;if(i=="class"){str+=" class='"+data.attributes[i]+" ";if(data.state=="closed"||data.state=="open")str+=" "+data.state+" ";str+="' ";cls=true}else str+=" "+i+"='"+data.attributes[i]+"' "}}if(!cls&&(data.state=="closed"||data.state=="open"))str+=" class='"+data.state+"' ";str+=">";if(this.settings.languages.length){for(var i=0;i<this.settings.languages.length;i++){var attr={};attr["href"]="#";attr["style"]="";attr["class"]=this.settings.languages[i];if(data.data[this.settings.languages[i]]&&(typeof data.data[this.settings.languages[i]].attributes).toLowerCase()!="undefined"){for(var j in data.data[this.settings.languages[i]].attributes){if(typeof data.data[this.settings.languages[i]].attributes[j]=="function")continue;if(j=="style"||j=="class")attr[j]+=" "+data.data[this.settings.languages[i]].attributes[j];else attr[j]=data.data[this.settings.languages[i]].attributes[j]}}if(data.data[this.settings.languages[i]]&&data.data[this.settings.languages[i]].icon&&this.settings.theme_name!="themeroller"){var icn=data.data[this.settings.languages[i]].icon.indexOf("/")==-1?this.theme+data.data[this.settings.languages[i]].icon:data.data[this.settings.languages[i]].icon;attr["style"]+=" ; background-image:url('"+icn+"'); "}str+="<a";for(var j in attr){if(typeof attr[j]=="function")continue;str+=' '+j+'="'+attr[j]+'" '}str+=">";if(data.data[this.settings.languages[i]]&&data.data[this.settings.languages[i]].icon&&this.settings.theme_name=="themeroller"){str+="<ins class='ui-icon "+data.data[this.settings.languages[i]].icon+"'>&nbsp;</ins>"}str+=((typeof data.data[this.settings.languages[i]].title).toLowerCase()!="undefined"?data.data[this.settings.languages[i]].title:data.data[this.settings.languages[i]])+"</a>"}}else{var attr={};attr["href"]="#";attr["style"]="";attr["class"]="";if((typeof data.data.attributes).toLowerCase()!="undefined"){for(var i in data.data.attributes){if(typeof data.data.attributes[i]=="function")continue;if(i=="style"||i=="class")attr[i]+=" "+data.data.attributes[i];else attr[i]=data.data.attributes[i]}}if(data.data.icon&&this.settings.ui.theme_name!="themeroller"){var icn=data.data.icon.indexOf("/")==-1?this.theme+data.data.icon:data.data.icon;attr["style"]+=" ; background-image:url('"+icn+"');"}str+="<a";for(var i in attr){if(typeof attr[j]=="function")continue;str+=' '+i+'="'+attr[i]+'" '}str+=">";if(data.data.icon&&this.settings.ui.theme_name=="themeroller"){str+="<ins class='ui-icon "+data.data.icon+"'>&nbsp;</ins>"}str+=((typeof data.data.title).toLowerCase()!="undefined"?data.data.title:data.data)+"</a>"}if(data.children&&data.children.length){str+='<ul>';for(var i=0;i<data.children.length;i++){str+=this.parseJSON(data.children[i])}str+='</ul>'}str+="</li>";return str},getJSON:function(nod,outer_attrib,inner_attrib,force){var _this=this;if(!nod||$(nod).size()==0){nod=this.container.children("ul").children("li")}else nod=$(nod);if(nod.size()>1){var arr=[];nod.each(function(){arr.push(_this.getJSON(this,outer_attrib,inner_attrib,force))});return arr}if(!outer_attrib)outer_attrib=["id","rel","class"];if(!inner_attrib)inner_attrib=[];var obj={attributes:{},data:false};for(var i in outer_attrib){if(typeof outer_attrib[i]=="function")continue;var val=(outer_attrib[i]=="class")?nod.attr(outer_attrib[i]).replace("last","").replace("leaf","").replace("closed","").replace("open",""):nod.attr(outer_attrib[i]);if(typeof val!="undefined"&&val.replace(" ","").length>0)obj.attributes[outer_attrib[i]]=val;delete val}if(this.settings.languages.length){obj.data={};for(var i in this.settings.languages){if(typeof this.settings.languages[i]=="function")continue;var a=nod.children("a."+this.settings.languages[i]);if(force||inner_attrib.length||a.get(0).style.backgroundImage.toString().length){obj.data[this.settings.languages[i]]={};obj.data[this.settings.languages[i]].title=a.text();if(a.get(0).style.backgroundImage.length){obj.data[this.settings.languages[i]].icon=a.get(0).style.backgroundImage.replace("url(","").replace(")","")}if(this.settings.ui.theme_name=="themeroller"&&a.children("ins").size()){var tmp=a.children("ins").attr("class");var cls=false;$.each(tmp.split(" "),function(i,val){if(val.indexOf("ui-icon-")==0){cls=val;return false}});if(cls)obj.data[this.settings.languages[i]].icon=cls}if(inner_attrib.length){obj.data[this.settings.languages[i]].attributes={};for(var j in inner_attrib){if(typeof inner_attrib[j]=="function")continue;var val=a.attr(inner_attrib[j]);if(typeof val!="undefined"&&val.replace(" ","").length>0)obj.data[this.settings.languages[i]].attributes[inner_attrib[j]]=val;delete val}}}else{obj.data[this.settings.languages[i]]=a.text()}}}else{var a=nod.children("a");if(force||inner_attrib.length||a.get(0).style.backgroundImage.toString().length){obj.data={};obj.data.title=a.text();if(a.get(0).style.backgroundImage.length){obj.data.icon=a.get(0).style.backgroundImage.replace("url(","").replace(")","")}if(this.settings.ui.theme_name=="themeroller"&&a.children("ins").size()){var tmp=a.children("ins").attr("class");var cls=false;$.each(tmp.split(" "),function(i,val){if(val.indexOf("ui-icon-")==0){cls=val;return false}});if(cls)obj.data[this.settings.languages[i]].icon=cls}if(inner_attrib.length){obj.data.attributes={};for(var j in inner_attrib){if(typeof inner_attrib[j]=="function")continue;var val=a.attr(inner_attrib[j]);if(typeof val!="undefined"&&val.replace(" ","").length>0)obj.data.attributes[inner_attrib[j]]=val;delete val}}}else{obj.data=a.text()}}if(nod.children("ul").size()>0){obj.children=[];nod.children("ul").children("li").each(function(){obj.children.push(_this.getJSON(this,outer_attrib,inner_attrib,force))})}return obj},getXML:function(tp,nod,outer_attrib,inner_attrib,cb){var _this=this;if(tp!="flat")tp="nested";if(!nod||$(nod).size()==0){nod=this.container.children("ul").children("li")}else nod=$(nod);if(nod.size()>1){var obj='<root>';nod.each(function(){obj+=_this.getXML(tp,this,outer_attrib,inner_attrib,true)});obj+='</root>';return obj}if(!outer_attrib)outer_attrib=["id","rel","class"];if(!inner_attrib)inner_attrib=[];var obj='';if(!cb)obj='<root>';obj+='<item ';if(tp=="flat"){var tmp_id=nod.parents("li:eq(0)").size()?nod.parents("li:eq(0)").attr("id"):0;obj+=' parent_id="'+tmp_id+'" ';delete tmp_id}for(var i in outer_attrib){if(typeof outer_attrib[i]=="function")continue;var val=(outer_attrib[i]=="class")?nod.attr(outer_attrib[i]).replace("last","").replace("leaf","").replace("closed","").replace("open",""):nod.attr(outer_attrib[i]);if(typeof val!="undefined"&&val.replace(" ","").length>0)obj+=' '+outer_attrib[i]+'="'+val+'" ';delete val}obj+='>';obj+='<content>';if(this.settings.languages.length){for(var i in this.settings.languages){if(typeof this.settings.languages[i]=="function")continue;var a=nod.children("a."+this.settings.languages[i]);obj+='<name ';if(inner_attrib.length||a.get(0).style.backgroundImage.toString().length||this.settings.ui.theme_name=="themeroller"){if(a.get(0).style.backgroundImage.length){obj+=' icon="'+a.get(0).style.backgroundImage.replace("url(","").replace(")","")+'" '}if(this.settings.ui.theme_name=="themeroller"&&a.children("ins").size()){var tmp=a.children("ins").attr("class");var cls=false;$.each(tmp.split(" "),function(i,val){if(val.indexOf("ui-icon-")==0){cls=val;return false}});if(cls)obj+=' icon="'+cls+'" '}if(inner_attrib.length){for(var j in inner_attrib){if(typeof inner_attrib[j]=="function")continue;var val=a.attr(inner_attrib[j]);if(typeof val!="undefined"&&val.replace(" ","").length>0)obj+=' '+inner_attrib[j]+'="'+val+'" ';delete val}}}obj+='><![CDATA['+a.text()+']]></name>'}}else{var a=nod.children("a");obj+='<name ';if(inner_attrib.length||a.get(0).style.backgroundImage.toString().length||this.settings.ui.theme_name=="themeroller"){if(a.get(0).style.backgroundImage.length){obj+=' icon="'+a.get(0).style.backgroundImage.replace("url(","").replace(")","")+'" '}if(this.settings.ui.theme_name=="themeroller"&&a.children("ins").size()){var tmp=a.children("ins").attr("class");var cls=false;$.each(tmp.split(" "),function(i,val){if(val.indexOf("ui-icon-")==0){cls=val;return false}});if(cls)obj+=' icon="'+cls+'" '}if(inner_attrib.length){for(var j in inner_attrib){if(typeof inner_attrib[j]=="function")continue;var val=a.attr(inner_attrib[j]);if(typeof val!="undefined"&&val.replace(" ","").length>0)obj+=' '+inner_attrib[j]+'="'+val+'" ';delete val}}}obj+='><![CDATA['+a.text()+']]></name>'}obj+='</content>';if(tp=="flat")obj+='</item>';if(nod.children("ul").size()>0){nod.children("ul").children("li").each(function(){obj+=_this.getXML(tp,this,outer_attrib,inner_attrib,true)})}if(tp=="nested")obj+='</item>';if(!cb)obj+='</root>';return obj},focus:function(){if(this.locked)return false;if(tree_component.focused!=this.cntr){tree_component.focused=this.cntr;this.settings.callback.onfocus.call(null,this)}},show_context:function(obj){this.context.show();var tmp=$(obj).children("a:visible").offset();this.context.css({"left":(tmp.left),"top":(tmp.top+parseInt(obj.children("a:visible").height())+2)})},hide_context:function(){if(this.context.to_remove&&this.context.apply_to)this.context.apply_to.children("a").removeClass("clicked");this.context.apply_to=false;this.context.hide()},attachEvents:function(){var _this=this;this.container.bind("mousedown.jstree",function(event){if(tree_component.drag_drop.isdown){tree_component.drag_drop.move_type=false;event.preventDefault();event.stopPropagation();event.stopImmediatePropagation();return false}}).bind("mouseup.jstree",function(event){setTimeout(function(){_this.focus.apply(_this)},5)}).bind("click.jstree",function(event){return true});$("#"+this.container.attr("id")+" li").live("click",function(event){if(event.target.tagName!="LI")return true;_this.off_height();if(event.pageY-$(event.target).offset().top>_this.li_height)return true;_this.toggle_branch.apply(_this,[event.target]);event.stopPropagation();return false});$("#"+this.container.attr("id")+" li a").live("click.jstree",function(event){if(event.which&&event.which==3)return true;if(_this.locked){event.preventDefault();event.target.blur();return _this.error("LOCKED")}_this.select_branch.apply(_this,[event.target,event.ctrlKey||_this.settings.rules.multiple=="on"]);if(_this.inp){_this.inp.blur()}event.preventDefault();event.target.blur();return false}).live("dblclick.jstree",function(event){if(_this.locked){event.preventDefault();event.stopPropagation();event.target.blur();return _this.error("LOCKED")}_this.settings.callback.ondblclk.call(null,_this.get_node(event.target).get(0),_this);event.preventDefault();event.stopPropagation();event.target.blur()}).live("contextmenu.jstree",function(event){if(_this.locked){event.target.blur();return _this.error("LOCKED")}var val=_this.settings.callback.onrgtclk.call(null,_this.get_node(event.target).get(0),_this,event);if(_this.context){if(_this.context.append==false){$("body").append(_this.context);_this.context.append=true;for(var i in _this.settings.ui.context){if(typeof _this.settings.ui.context[i]=="function")continue;if(_this.settings.ui.context[i]=="separator")continue;(function(){var func=_this.settings.ui.context[i].action;_this.context.children("[rel="+_this.settings.ui.context[i].id+"]").bind("click",function(event){if(!$(this).hasClass("disabled")){func.call(null,_this.context.apply_to||null,_this);_this.hide_context()}event.stopPropagation();event.preventDefault();return false}).bind("mouseup",function(event){this.blur();if($(this).hasClass("disabled")){event.stopPropagation();event.preventDefault();return false}}).bind("mousedown",function(event){event.stopPropagation();event.preventDefault()})})()}}var obj=_this.get_node(event.target);if(_this.inp){_this.inp.blur()}if(obj){if(!obj.children("a:eq(0)").hasClass("clicked")){_this.context.apply_to=obj;_this.context.to_remove=true;_this.context.apply_to.children("a").addClass("clicked");event.target.blur()}else{_this.context.to_remove=false;_this.context.apply_to=(_this.selected_arr&&_this.selected_arr.length>1)?_this.selected_arr:_this.selected}_this.context.children("a").removeClass("disabled").show();var go=false;for(var i in _this.settings.ui.context){if(typeof _this.settings.ui.context[i]=="function")continue;if(_this.settings.ui.context[i]=="separator")continue;var state=_this.settings.ui.context[i].visible.call(null,_this.context.apply_to,_this);if(state===false)_this.context.children("[rel="+_this.settings.ui.context[i].id+"]").addClass("disabled");if(state===-1)_this.context.children("[rel="+_this.settings.ui.context[i].id+"]").hide();else go=true}if(go==true)_this.show_context(obj);event.preventDefault();event.stopPropagation();return false}}return val}).live("mouseover.jstree",function(event){if(_this.locked){event.preventDefault();event.stopPropagation();return _this.error("LOCKED")}if((_this.settings.ui.hover_mode||_this.settings.ui.theme_name=="themeroller")&&_this.hovered!==false&&event.target.tagName=="A"){_this.hovered.children("a").removeClass("hover ui-state-hover");_this.hovered=false}if(_this.settings.ui.theme_name=="themeroller"){_this.hover_branch.apply(_this,[event.target])}});if(_this.settings.ui.theme_name=="themeroller"){$("#"+this.container.attr("id")+" li a").live("mouseout",function(event){if(_this.hovered)_this.hovered.children("a").removeClass("hover ui-state-hover")})}if(this.settings.rules.draggable!="none"){$("#"+this.container.attr("id")+" li a").live("mousedown.jstree",function(event){if(_this.settings.rules.drag_button=="left"&&event.which&&event.which!=1)return true;if(_this.settings.rules.drag_button=="right"&&event.which&&event.which!=3)return true;_this.focus.apply(_this);if(_this.locked)return _this.error("LOCKED");var obj=_this.get_node(event.target);if(_this.settings.rules.multiple!=false&&_this.selected_arr.length>1&&obj.children("a:eq(0)").hasClass("clicked")){var counter=0;for(var i in _this.selected_arr){if(typeof _this.selected_arr[i]=="function")continue;if(_this.check("draggable",_this.selected_arr[i])){_this.selected_arr[i].addClass("dragged");tree_component.drag_drop.origin_tree=_this;counter++}}if(counter>0){if(_this.check("draggable",obj))tree_component.drag_drop.drag_node=obj;else tree_component.drag_drop.drag_node=_this.container.find("li.dragged:eq(0)");tree_component.drag_drop.isdown=true;tree_component.drag_drop.drag_help=$("<div id='jstree-dragged' class='tree "+(_this.container.hasClass("tree-default")?" tree-default":"")+(_this.settings.ui.theme_name&&_this.settings.ui.theme_name!="default"?" tree-"+_this.settings.ui.theme_name:"")+"' />").append("<ul class='"+_this.container.children("ul:eq(0)").get(0).className+"' />");var tmp=$(tree_component.drag_drop.drag_node.get(0).cloneNode(true));if(_this.settings.languages.length>0)tmp.find("a").not("."+_this.current_lang).hide();tree_component.drag_drop.drag_help.children("ul:eq(0)").append(tmp);tree_component.drag_drop.drag_help.find("li:eq(0)").removeClass("last").addClass("last").children("a").html("Multiple selection").end().children("ul").remove()}}else{if(_this.check("draggable",obj)){tree_component.drag_drop.drag_node=obj;tree_component.drag_drop.drag_help=$("<div id='jstree-dragged' class='tree "+(_this.container.hasClass("tree-default")?" tree-default":"")+(_this.settings.ui.theme_name&&_this.settings.ui.theme_name!="default"?" tree-"+_this.settings.ui.theme_name:"")+"' />").append("<ul class='"+_this.container.children("ul:eq(0)").get(0).className+"' />");var tmp=$(obj.get(0).cloneNode(true));if(_this.settings.languages.length>0)tmp.find("a").not("."+_this.current_lang).hide();tree_component.drag_drop.drag_help.children("ul:eq(0)").append(tmp);tree_component.drag_drop.drag_help.find("li:eq(0)").removeClass("last").addClass("last");tree_component.drag_drop.isdown=true;tree_component.drag_drop.foreign=false;tree_component.drag_drop.origin_tree=_this;obj.addClass("dragged")}}tree_component.drag_drop.init_x=event.pageX;tree_component.drag_drop.init_y=event.pageY;obj.blur();event.preventDefault();event.stopPropagation();return false});$(document).bind("mousedown.jstree",tree_component.mousedown).bind("mouseup.jstree",tree_component.mouseup).bind("mousemove.jstree",tree_component.mousemove)}if(_this.context)$(document).bind("mousedown",function(){_this.hide_context()})},checkMove:function(NODES,REF_NODE,TYPE){if(this.locked)return this.error("LOCKED");var _this=this;if(REF_NODE.parents("li.dragged").size()>0||REF_NODE.is(".dragged"))return this.error("MOVE: NODE OVER SELF");if(NODES.size()==1){var NODE=NODES.eq(0);if(tree_component.drag_drop.foreign){if(this.settings.rules.droppable.length==0)return false;if(!NODE.is("."+this.settings.rules.droppable.join(", .")))return false;var ok=false;for(var i in this.settings.rules.droppable){if(typeof this.settings.rules.droppable[i]=="function")continue;if(NODE.is("."+this.settings.rules.droppable[i])){if(this.settings.rules.metadata){$.metadata.setType("attr",this.settings.rules.metadata);NODE.attr(this.settings.rules.metadata,"type: '"+this.settings.rules.droppable[i]+"'")}else{NODE.attr(this.settings.rules.type_attr,this.settings.rules.droppable[i])}ok=true;break}}if(!ok)return false}if(!this.check("dragrules",[NODE,TYPE,REF_NODE.parents("li:eq(0)")]))return this.error("MOVE: AGAINST DRAG RULES")}else{var ok=true;NODES.each(function(i){if(ok==false)return false;var ref=REF_NODE;var mv=TYPE;if(!_this.check.apply(_this,["dragrules",[$(this),mv,ref]]))ok=false});if(ok==false)return this.error("MOVE: AGAINST DRAG RULES")}if(this.settings.rules.use_inline&&this.settings.rules.metadata){var nd=false;if(TYPE=="inside")nd=REF_NODE.parents("li:eq(0)");else nd=REF_NODE.parents("li:eq(1)");if(nd.size()){if(typeof nd.metadata()["valid_children"]!="undefined"){var tmp=nd.metadata()["valid_children"];var ok=true;NODES.each(function(i){if(ok==false)return false;if($.inArray(_this.get_type(this),tmp)==-1)ok=false});if(ok==false)return this.error("MOVE: NOT A VALID CHILD")}if(typeof nd.metadata()["max_children"]!="undefined"){if((nd.children("ul:eq(0)").children("li").not(".dragged").size()+NODES.size())>nd.metadata().max_children)return this.error("MOVE: MAX CHILDREN REACHED")}var incr=0;NODES.each(function(j){var i=1;var t=$(this);while(i<100){t=t.children("ul").children("li");if(t.size()==0)break;i++}incr=Math.max(i,incr)});var ok=true;if((typeof $(nd).metadata().max_depth).toLowerCase()!="undefined"&&$(nd).metadata().max_depth<incr)ok=false;else{nd.parents("li").each(function(i){if(ok==false)return false;if((typeof $(this).metadata().max_depth).toLowerCase()!="undefined"){if((i+incr)>=$(this).metadata().max_depth)ok=false}})}if(ok==false)return this.error("MOVE: MAX_DEPTH REACHED")}}return true},reselect:function(is_callback){var _this=this;if(!is_callback)this.cl_count=0;else this.cl_count--;if(this.opened&&this.opened.length){var opn=false;for(var j=0;this.opened&&j<this.opened.length;j++){if(this.settings.data.async){if(this.get_node(this.opened[j]).size()>0){opn=true;var tmp=this.opened[j];delete this.opened[j];this.open_branch(tmp,true,function(){_this.reselect.apply(_this,[true])});this.cl_count++}}else this.open_branch(this.opened[j],true)}if(this.settings.data.async&&opn)return;delete this.opened}if(this.cl_count>0)return;if(this.settings.ui.rtl)this.container.css("direction","rtl").children("ul:eq(0)").addClass("rtl");else this.container.css("direction","ltr").children("ul:eq(0)").addClass("ltr");if(this.settings.ui.dots==false)this.container.children("ul:eq(0)").addClass("no_dots");if(this.scrtop){this.container.scrollTop(_this.scrtop);delete this.scrtop}if(this.settings.selected!==false){$.each(this.settings.selected,function(i){if(_this.is_partial_refresh)_this.select_branch($(_this.settings.selected[i],_this.container),(_this.settings.rules.multiple!==false));else _this.select_branch($(_this.settings.selected[i],_this.container),(_this.settings.rules.multiple!==false&&i>0))});this.settings.selected=false}if(this.settings.ui.theme_name=="themeroller")this.container.find("a").addClass("ui-state-default");this.settings.callback.onload.call(null,_this)},get_node:function(obj){var obj=$(obj);return obj.is("li")?obj:obj.parents("li:eq(0)")},get_type:function(obj){obj=!obj?this.selected:this.get_node(obj);if(!obj)return;if(this.settings.rules.metadata){$.metadata.setType("attr",this.settings.rules.metadata);var tmp=obj.metadata().type;if(tmp)return tmp}return obj.attr(this.settings.rules.type_attr)},scrollCheck:function(x,y){var _this=this;var cnt=_this.container;var off=_this.container.offset();var st=cnt.scrollTop();var sl=cnt.scrollLeft();var h_cor=(cnt.get(0).scrollWidth>cnt.width())?40:20;if(y-off.top<20)cnt.scrollTop(Math.max((st-_this.settings.ui.scroll_spd),0));if(cnt.height()-(y-off.top)<h_cor)cnt.scrollTop(st+_this.settings.ui.scroll_spd);if(x-off.left<20)cnt.scrollLeft(Math.max((sl-_this.settings.ui.scroll_spd),0));if(cnt.width()-(x-off.left)<40)cnt.scrollLeft(sl+_this.settings.ui.scroll_spd);if(cnt.scrollLeft()!=sl||cnt.scrollTop()!=st){_this.moveType=false;_this.moveRef=false;tree_component.drag_drop.marker.hide()}tree_component.drag_drop.scroll_time=setTimeout(function(){_this.scrollCheck(x,y)},50)},check:function(rule,nodes){if(this.locked)return this.error("LOCKED");if(rule!="dragrules"&&this.settings.rules.use_inline&&this.settings.rules.metadata){$.metadata.setType("attr",this.settings.rules.metadata);if(typeof this.get_node(nodes).metadata()[rule]!="undefined")return this.get_node(nodes).metadata()[rule]}if(!this.settings.rules[rule])return false;if(this.settings.rules[rule]=="none")return false;if(this.settings.rules[rule]=="all")return true;if(rule=="dragrules"){var nds=new Array();nds[0]=this.get_type(nodes[0]);nds[1]=nodes[1];nds[2]=this.get_type(nodes[2]);for(var i=0;i<this.settings.rules.dragrules.length;i++){var r=this.settings.rules.dragrules[i];var n=(r.indexOf("!")===0)?false:true;if(!n)r=r.replace("!","");var tmp=r.split(" ");for(var j=0;j<3;j++){if(tmp[j]==nds[j]||tmp[j]=="*")tmp[j]=true}if(tmp[0]===true&&tmp[1]===true&&tmp[2]===true)return n}return false}else return($.inArray(this.get_type(nodes),this.settings.rules[rule])!=-1)?true:false},hover_branch:function(obj){if(this.locked)return this.error("LOCKED");if(this.settings.ui.hover_mode==false&&this.settings.ui.theme_name!="themeroller")return this.select_branch(obj);var _this=this;var obj=_this.get_node(obj);if(!obj.size())return this.error("HOVER: NOT A VALID NODE");if(!_this.check("clickable",obj))return this.error("SELECT: NODE NOT SELECTABLE");if(this.hovered)this.hovered.children("A").removeClass("hover ui-state-hover");this.hovered=obj;this.hovered.children("a").removeClass("hover ui-state-hover").addClass(this.settings.ui.theme_name=="themeroller"?"hover ui-state-hover":"hover");var off_t=this.hovered.offset().top;var beg_t=this.container.offset().top;var end_t=beg_t+this.container.height();var h_cor=(this.container.get(0).scrollWidth>this.container.width())?40:20;if(off_t+5<beg_t)this.container.scrollTop(this.container.scrollTop()-(beg_t-off_t+5));if(off_t+h_cor>end_t)this.container.scrollTop(this.container.scrollTop()+(off_t+h_cor-end_t))},select_branch:function(obj,multiple){if(this.locked)return this.error("LOCKED");if(!obj&&this.hovered!==false)obj=this.hovered;var _this=this;obj=_this.get_node(obj);if(!obj.size())return this.error("SELECT: NOT A VALID NODE");obj.children("a").removeClass("hover ui-state-hover");if(!_this.check("clickable",obj))return this.error("SELECT: NODE NOT SELECTABLE");if(_this.settings.callback.beforechange.call(null,obj.get(0),_this)===false)return this.error("SELECT: STOPPED BY USER");if(this.settings.rules.multiple!=false&&multiple&&obj.children("a.clicked").size()>0){return this.deselect_branch(obj)}if(this.settings.rules.multiple!=false&&multiple){this.selected_arr.push(obj)}if(this.settings.rules.multiple!=false&&!multiple){for(var i in this.selected_arr){if(typeof this.selected_arr[i]=="function")continue;this.selected_arr[i].children("A").removeClass("clicked ui-state-active");this.settings.callback.ondeselect.call(null,this.selected_arr[i].get(0),_this)}this.selected_arr=[];this.selected_arr.push(obj);if(this.selected&&this.selected.children("A").hasClass("clicked")){this.selected.children("A").removeClass("clicked ui-state-active");this.settings.callback.ondeselect.call(null,this.selected.get(0),_this)}}if(!this.settings.rules.multiple){if(this.selected){this.selected.children("A").removeClass("clicked ui-state-active");this.settings.callback.ondeselect.call(null,this.selected.get(0),_this)}}this.selected=obj;if((this.settings.ui.hover_mode||this.settings.ui.theme_name=="themeroller")&&this.hovered!==false){this.hovered.children("A").removeClass("hover ui-state-hover");this.hovered=obj}this.selected.children("a").removeClass("clicked ui-state-active").addClass(this.settings.ui.theme_name=="themeroller"?"clicked ui-state-active":"clicked").end().parents("li.closed").each(function(){_this.open_branch(this,true)});var off_t=this.selected.offset().top;var beg_t=this.container.offset().top;var end_t=beg_t+this.container.height();var h_cor=(this.container.get(0).scrollWidth>this.container.width())?40:20;if(off_t+5<beg_t)this.container.scrollTop(this.container.scrollTop()-(beg_t-off_t+5));if(off_t+h_cor>end_t)this.container.scrollTop(this.container.scrollTop()+(off_t+h_cor-end_t));this.set_cookie("selected");this.settings.callback.onselect.call(null,this.selected.get(0),_this);this.settings.callback.onchange.call(null,this.selected.get(0),_this)},deselect_branch:function(obj){if(this.locked)return this.error("LOCKED");var _this=this;var obj=this.get_node(obj);obj.children("a").removeClass("clicked ui-state-active");this.settings.callback.ondeselect.call(null,obj.get(0),_this);if(this.settings.rules.multiple!=false&&this.selected_arr.length>1){this.selected_arr=[];this.container.find("a.clicked").filter(":first-child").parent().each(function(){_this.selected_arr.push($(this))});if(obj.get(0)==this.selected.get(0)){this.selected=this.selected_arr[0];this.set_cookie("selected")}}else{if(this.settings.rules.multiple!=false)this.selected_arr=[];this.selected=false;this.set_cookie("selected")}if(this.selected)this.settings.callback.onchange.call(null,this.selected.get(0),_this);else this.settings.callback.onchange.call(null,false,_this)},toggle_branch:function(obj){if(this.locked)return this.error("LOCKED");var obj=this.get_node(obj);if(obj.hasClass("closed"))return this.open_branch(obj);if(obj.hasClass("open"))return this.close_branch(obj)},open_branch:function(obj,disable_animation,callback){if(this.locked)return this.error("LOCKED");var obj=this.get_node(obj);if(!obj.size())return this.error("OPEN: NO SUCH NODE");if(obj.hasClass("leaf"))return this.error("OPEN: OPENING LEAF NODE");if(this.settings.data.async&&obj.find("li").size()==0){if(this.settings.callback.beforeopen.call(null,obj.get(0),this)===false)return this.error("OPEN: STOPPED BY USER");var _this=this;obj.children("ul:eq(0)").remove().end().append("<ul><li class='last'><a class='loading' href='#'>"+(_this.settings.lang.loading||"Loading ...")+"</a></li></ul>");obj.removeClass("closed").addClass("open");if(this.settings.data.type=="xml_flat"||this.settings.data.type=="xml_nested"){var xsl=(this.settings.data.type=="xml_flat")?"flat.xsl":"nested.xsl";obj.children("ul:eq(0)").getTransform(this.path+xsl,this.settings.data.url,{params:{theme_path:_this.theme},meth:this.settings.data.method,dat:this.settings.data.async_data(obj,this),repl:true,callback:function(str,json){if(str.length<15){obj.removeClass("closed").removeClass("open").addClass("leaf").children("ul").remove();if(callback)callback.call();return}_this.open_branch.apply(_this,[obj]);if(callback)callback.call()},error:function(){obj.removeClass("open").addClass("closed").children("ul:eq(0)").remove()}})}else{$.ajax({type:this.settings.data.method,url:this.settings.data.url,data:this.settings.data.async_data(obj,this),dataType:"json",success:function(data,textStatus){data=_this.settings.callback.onJSONdata.call(null,data,_this);if(!data||data.length==0){obj.removeClass("closed").removeClass("open").addClass("leaf").children("ul").remove();if(callback)callback.call();return}var str="";if(data.length){for(var i=0;i<data.length;i++){str+=_this.parseJSON(data[i])}}else str=_this.parseJSON(data);if(str.length>0){obj.children("ul:eq(0)").replaceWith("<ul>"+str+"</ul>");obj.find("li:last-child").addClass("last").end().find("li:has(ul)").not(".open").addClass("closed");obj.find("li").not(".open").not(".closed").addClass("leaf");_this.open_branch.apply(_this,[obj])}else obj.removeClass("closed").removeClass("open").addClass("leaf").children("ul").remove();if(callback)callback.call()},error:function(xhttp,textStatus,errorThrown){obj.removeClass("open").addClass("closed").children("ul:eq(0)").remove();_this.error(errorThrown+" "+textStatus)}})}return true}else{if(!this.settings.data.async){if(this.settings.callback.beforeopen.call(null,obj.get(0),this)===false)return this.error("OPEN: STOPPED BY USER")}if(this.settings.ui.theme_name=="themeroller")obj.find("a").not(".ui-state-default").addClass("ui-state-default");if(parseInt(this.settings.ui.animation)>0&&!disable_animation){obj.children("ul:eq(0)").css("display","none");obj.removeClass("closed").addClass("open");obj.children("ul:eq(0)").slideDown(parseInt(this.settings.ui.animation),function(){$(this).css("display","");if(callback)callback.call()})}else{obj.removeClass("closed").addClass("open");if(callback)callback.call()}this.set_cookie("open");this.settings.callback.onopen.call(null,obj.get(0),this);return true}},close_branch:function(obj,disable_animation){if(this.locked)return this.error("LOCKED");var _this=this;var obj=this.get_node(obj);if(!obj.size())return this.error("CLOSE: NO SUCH NODE");if(_this.settings.callback.beforeclose.call(null,obj.get(0),_this)===false)return this.error("CLOSE: STOPPED BY USER");if(parseInt(this.settings.ui.animation)>0&&!disable_animation&&obj.children("ul:eq(0)").size()==1){obj.children("ul:eq(0)").slideUp(parseInt(this.settings.ui.animation),function(){if(obj.hasClass("open"))obj.removeClass("open").addClass("closed");_this.set_cookie("open");$(this).css("display","")})}else{if(obj.hasClass("open"))obj.removeClass("open").addClass("closed");this.set_cookie("open")}if(this.selected&&obj.children("ul:eq(0)").find("a.clicked").size()>0){obj.find("li:has(a.clicked)").each(function(){_this.deselect_branch(this)});if(obj.children("a.clicked").size()==0)this.select_branch(obj,(this.settings.rules.multiple!=false&&this.selected_arr.length>0))}this.settings.callback.onclose.call(null,obj.get(0),this)},open_all:function(obj,callback){if(this.locked)return this.error("LOCKED");var _this=this;obj=obj?this.get_node(obj).parent():this.container;var s=obj.find("li.closed").size();if(!callback)this.cl_count=0;else this.cl_count--;if(s>0){this.cl_count+=s;obj.find("li.closed").each(function(){var __this=this;_this.open_branch.apply(_this,[this,true,function(){_this.open_all.apply(_this,[__this,true])}])})}else if(this.cl_count==0)this.settings.callback.onopen_all.call(null,this)},close_all:function(){if(this.locked)return this.error("LOCKED");var _this=this;this.container.find("li.open").each(function(){_this.close_branch(this,true)})},show_lang:function(i){if(this.locked)return this.error("LOCKED");if(this.settings.languages[i]==this.current_lang)return true;var st=false;var id=this.container.attr("id")?"#"+this.container.attr("id"):".tree";st=get_css(id+" ."+this.current_lang,this.sn);if(st!==false)st.style.display="none";st=get_css(id+" ."+this.settings.languages[i],this.sn);if(st!==false)st.style.display="";this.current_lang=this.settings.languages[i];return true},cycle_lang:function(){if(this.locked)return this.error("LOCKED");var i=$.inArray(this.current_lang,this.settings.languages);i++;if(i>this.settings.languages.length-1)i=0;this.show_lang(i)},create:function(obj,ref_node,position){if(this.locked)return this.error("LOCKED");var root=false;if(ref_node==-1){root=true;ref_node=this.container}else ref_node=ref_node?this.get_node(ref_node):this.selected;if(!root&&(!ref_node||!ref_node.size()))return this.error("CREATE: NO NODE SELECTED");var pos=position;var tmp=ref_node;if(position=="before"){position=ref_node.parent().children().index(ref_node);ref_node=ref_node.parents("li:eq(0)")}if(position=="after"){position=ref_node.parent().children().index(ref_node)+1;ref_node=ref_node.parents("li:eq(0)")}if(!root&&ref_node.size()==0){root=true;ref_node=this.container}if(!root){if(!this.check("creatable",ref_node))return this.error("CREATE: CANNOT CREATE IN NODE");if(ref_node.hasClass("closed")){if(this.settings.data.async&&ref_node.children("ul").size()==0){var _this=this;return this.open_branch(ref_node,true,function(){_this.create.apply(_this,[obj,ref_node,position])})}else this.open_branch(ref_node,true)}}var torename=false;if(!obj)obj={};else obj=$.extend(true,{},obj);if(!obj.attributes)obj.attributes={};if(this.settings.rules.metadata){if(!obj.attributes[this.settings.rules.metadata])obj.attributes[this.settings.rules.metadata]='{ "type" : "'+(this.get_type(tmp)||"")+'" }'}else{if(!obj.attributes[this.settings.rules.type_attr])obj.attributes[this.settings.rules.type_attr]=this.get_type(tmp)||""}if(this.settings.languages.length){if(!obj.data){obj.data={};torename=true}for(var i=0;i<this.settings.languages.length;i++){if(!obj.data[this.settings.languages[i]])obj.data[this.settings.languages[i]]=((typeof this.settings.lang.new_node).toLowerCase()!="string"&&this.settings.lang.new_node[i])?this.settings.lang.new_node[i]:this.settings.lang.new_node}}else{if(!obj.data){obj.data=this.settings.lang.new_node;torename=true}}var $li=$(this.parseJSON(obj));if($li.children("ul").size()){if(!$li.is(".open"))$li.addClass("closed")}else $li.addClass("leaf");$li.find("li:last-child").addClass("last").end().find("li:has(ul)").not(".open").addClass("closed");$li.find("li").not(".open").not(".closed").addClass("leaf");if(!root&&this.settings.rules.use_inline&&this.settings.rules.metadata){var t=this.get_type($li)||"";$.metadata.setType("attr",this.settings.rules.metadata);if(typeof ref_node.metadata()["valid_children"]!="undefined"){if($.inArray(t,ref_node.metadata()["valid_children"])==-1)return this.error("CREATE: NODE NOT A VALID CHILD")}if(typeof ref_node.metadata()["max_children"]!="undefined"){if((ref_node.children("ul:eq(0)").children("li").size()+1)>ref_node.metadata().max_children)return this.error("CREATE: MAX_CHILDREN REACHED")}var ok=true;if((typeof $(ref_node).metadata().max_depth).toLowerCase()!="undefined"&&$(ref_node).metadata().max_depth===0)ok=false;else{ref_node.parents("li").each(function(i){if($(this).metadata().max_depth){if((i+1)>=$(this).metadata().max_depth){ok=false;return false}}})}if(!ok)return this.error("CREATE: MAX_DEPTH REACHED")}if((typeof position).toLowerCase()=="undefined"||position=="inside")position=(this.settings.rules.createat=="top")?0:ref_node.children("ul:eq(0)").children("li").size();if(ref_node.children("ul").size()==0||(root==true&&ref_node.children("ul").children("li").size()==0)){if(!root)var a=this.moved($li,ref_node.children("a:eq(0)"),"inside",true);else var a=this.moved($li,this.container.children("ul:eq(0)"),"inside",true)}else if(pos=="before"&&ref_node.children("ul:eq(0)").children("li:nth-child("+(position+1)+")").size())var a=this.moved($li,ref_node.children("ul:eq(0)").children("li:nth-child("+(position+1)+")").children("a:eq(0)"),"before",true);else if(pos=="after"&&ref_node.children("ul:eq(0)").children("li:nth-child("+(position)+")").size())var a=this.moved($li,ref_node.children("ul:eq(0)").children("li:nth-child("+(position)+")").children("a:eq(0)"),"after",true);else if(ref_node.children("ul:eq(0)").children("li:nth-child("+(position+1)+")").size())var a=this.moved($li,ref_node.children("ul:eq(0)").children("li:nth-child("+(position+1)+")").children("a:eq(0)"),"before",true);else var a=this.moved($li,ref_node.children("ul:eq(0)").children("li:last").children("a:eq(0)"),"after",true);if(a===false)return this.error("CREATE: ABORTED");if(torename){this.select_branch($li.children("a:eq(0)"));this.rename()}return $li},rename:function(obj){if(this.locked)return this.error("LOCKED");obj=obj?this.get_node(obj):this.selected;var _this=this;if(!obj||!obj.size())return this.error("RENAME: NO NODE SELECTED");if(!this.check("renameable",obj))return this.error("RENAME: NODE NOT RENAMABLE");if(!this.settings.callback.beforerename.call(null,obj.get(0),_this.current_lang,_this))return this.error("RENAME: STOPPED BY USER");obj.parents("li.closed").each(function(){_this.open_branch(this)});if(this.current_lang)obj=obj.find("a."+this.current_lang).get(0);else obj=obj.find("a:first").get(0);last_value=obj.innerHTML;_this.inp=$("<input type='text' autocomplete='off' />");_this.inp.val(last_value.replace(/&amp;/g,"&").replace(/&gt;/g,">").replace(/&lt;/g,"<")).bind("mousedown",function(event){event.stopPropagation()}).bind("mouseup",function(event){event.stopPropagation()}).bind("click",function(event){event.stopPropagation()}).bind("keyup",function(event){var key=event.keyCode||event.which;if(key==27){this.value=last_value;this.blur();return}if(key==13){this.blur();return}});var rb={};rb[this.container.attr("id")]=this.get_rollback();_this.inp.blur(function(event){if(this.value=="")this.value=last_value;$(obj).text($(obj).parent().find("input").eq(0).attr("value")).get(0).style.display="";$(obj).prevAll("span").remove();_this.settings.callback.onrename.call(null,_this.get_node(obj).get(0),_this.current_lang,_this,rb);_this.inp=false});var spn=$("<span />").addClass(obj.className).append(_this.inp);spn.attr("style",$(obj).attr("style"));obj.style.display="none";$(obj).parent().prepend(spn);_this.inp.get(0).focus();_this.inp.get(0).select()},remove:function(obj){if(this.locked)return this.error("LOCKED");var rb={};rb[this.container.attr("id")]=this.get_rollback();if(obj&&(!this.selected||this.get_node(obj).get(0)!=this.selected.get(0))){obj=this.get_node(obj);if(obj.size()){if(!this.check("deletable",obj))return this.error("DELETE: NODE NOT DELETABLE");if(!this.settings.callback.beforedelete.call(null,obj.get(0),_this))return this.error("DELETE: STOPPED BY USER");$parent=obj.parent();obj=obj.remove();$parent.children("li:last").addClass("last");if($parent.children("li").size()==0){$li=$parent.parents("li:eq(0)");$li.removeClass("open").removeClass("closed").addClass("leaf").children("ul").remove();this.set_cookie("open")}this.settings.callback.ondelete.call(null,obj.get(0),this,rb)}}else if(this.selected){if(!this.check("deletable",this.selected))return this.error("DELETE: NODE NOT DELETABLE");if(!this.settings.callback.beforedelete.call(null,this.selected.get(0),_this))return this.error("DELETE: STOPPED BY USER");$parent=this.selected.parent();var obj=this.selected;if(this.settings.rules.multiple==false||this.selected_arr.length==1){var stop=true;var tmp=(this.selected.prev("li:eq(0)").size())?this.selected.prev("li:eq(0)"):this.selected.parents("li:eq(0)")}obj=obj.remove();$parent.children("li:last").addClass("last");if($parent.children("li").size()==0){$li=$parent.parents("li:eq(0)");$li.removeClass("open").removeClass("closed").addClass("leaf").children("ul").remove();this.set_cookie("open")}this.settings.callback.ondelete.call(null,obj.get(0),this,rb);if(stop&&tmp)this.select_branch(tmp);if(this.settings.rules.multiple!=false&&!stop){var _this=this;this.selected_arr=[];this.container.find("a.clicked").filter(":first-child").parent().each(function(){_this.selected_arr.push($(this))});if(this.selected_arr.length>0){this.selected=this.selected_arr[0];this.remove()}}}else return this.error("DELETE: NO NODE SELECTED")},next:function(obj,strict){obj=this.get_node(obj);if(!obj.size())return false;if(strict)return(obj.nextAll("li").size()>0)?obj.nextAll("li:eq(0)"):false;if(obj.hasClass("open"))return obj.find("li:eq(0)");else if(obj.nextAll("li").size()>0)return obj.nextAll("li:eq(0)");else return obj.parents("li").next("li").eq(0)},prev:function(obj,strict){obj=this.get_node(obj);if(!obj.size())return false;if(strict)return(obj.prevAll("li").size()>0)?obj.prevAll("li:eq(0)"):false;if(obj.prev("li").size()){var obj=obj.prev("li").eq(0);while(obj.hasClass("open"))obj=obj.children("ul:eq(0)").children("li:last");return obj}else return obj.parents("li:eq(0)").size()?obj.parents("li:eq(0)"):false},parent:function(obj){obj=this.get_node(obj);if(!obj.size())return false;return obj.parents("li:eq(0)").size()?obj.parents("li:eq(0)"):false},children:function(obj){obj=this.get_node(obj);if(!obj.size())return false;return obj.children("ul:eq(0)").children("li")},get_next:function(force){var obj=this.hovered||this.selected;return force?this.select_branch(this.next(obj)):this.hover_branch(this.next(obj))},get_prev:function(force){var obj=this.hovered||this.selected;return force?this.select_branch(this.prev(obj)):this.hover_branch(this.prev(obj))},get_left:function(force,rtl){if(this.settings.ui.rtl&&!rtl)return this.get_right(force,true);var obj=this.hovered||this.selected;if(obj){if(obj.hasClass("open"))this.close_branch(obj);else{return force?this.select_branch(this.parent(obj)):this.hover_branch(this.parent(obj))}}},get_right:function(force,rtl){if(this.settings.ui.rtl&&!rtl)return this.get_left(force,true);var obj=this.hovered||this.selected;if(obj){if(obj.hasClass("closed"))this.open_branch(obj);else{return force?this.select_branch(obj.find("li:eq(0)")):this.hover_branch(obj.find("li:eq(0)"))}}},toggleDots:function(){if(this.settings.ui.dots){this.settings.ui.dots=false;this.container.children("ul:eq(0)").addClass("no_dots")}else{this.settings.ui.dots=true;this.container.children("ul:eq(0)").removeClass("no_dots")}},toggleRTL:function(){if(this.settings.ui.rtl){this.settings.ui.rtl=false;this.container.css("direction","ltr").children("ul:eq(0)").removeClass("rtl").addClass("ltr")}else{this.settings.ui.rtl=true;this.container.css("direction","rtl").children("ul:eq(0)").removeClass("ltr").addClass("rtl")}},set_cookie:function(type){if(this.settings.cookies===false)return false;if(this.settings.cookies[type]===false)return false;switch(type){case"selected":if(this.settings.rules.multiple!=false&&this.selected_arr.length>1){var val=Array();$.each(this.selected_arr,function(){if(this.attr("id")){val.push(this.attr("id"))}});val=val.join(",")}else var val=this.selected?this.selected.attr("id"):false;$.cookie(this.settings.cookies.prefix+'_selected',val,this.settings.cookies.opts);break;case"open":var str="";this.container.find("li.open").each(function(i){if(this.id){str+=this.id+","}});$.cookie(this.settings.cookies.prefix+'_open',str.replace(/,$/ig,""),this.settings.cookies.opts);break}},get_rollback:function(){var rb={};if(this.context.to_remove&&this.context.apply_to)this.context.apply_to.children("a").removeClass("clicked");rb.html=this.container.html();if(this.context.to_remove&&this.context.apply_to)this.context.apply_to.children("a").addClass("clicked");rb.selected=this.selected?this.selected.attr("id"):false;return rb},moved:function(what,where,how,is_new,is_copy,rb){var what=$(what);var $parent=$(what).parents("ul:eq(0)");var $where=$(where);if(!rb){var rb={};rb[this.container.attr("id")]=this.get_rollback();if(!is_new){var tmp=what.size()>1?what.eq(0).parents(".tree:eq(0)"):what.parents(".tree:eq(0)");if(tmp.get(0)!=this.container.get(0)){tmp=tree_component.inst[tmp.attr("id")];rb[tmp.container.attr("id")]=tmp.get_rollback()}delete tmp}}if(how=="inside"&&this.settings.data.async&&this.get_node($where).hasClass("closed")){var _this=this;return this.open_branch(this.get_node($where),true,function(){_this.moved.apply(_this,[what,where,how,is_new,is_copy,rb])})}if(what.size()>1){var _this=this;var tmp=this.moved(what.eq(0),where,how,false,is_copy,rb);what.each(function(i){if(i==0)return;if(tmp){tmp=_this.moved(this,tmp.children("a:eq(0)"),"after",false,is_copy,rb)}});return}if(is_copy){_what=what.clone();_what.each(function(i){this.id=this.id+"_copy";$(this).find("li").each(function(){this.id=this.id+"_copy"});$(this).removeClass("dragged").find("a.clicked").removeClass("clicked ui-state-active").end().find("li.dragged").removeClass("dragged")})}else _what=what;if(is_new){if(!this.settings.callback.beforecreate.call(null,this.get_node(what).get(0),this.get_node(where).get(0),how,this))return false}else{if(!this.settings.callback.beforemove.call(null,this.get_node(what).get(0),this.get_node(where).get(0),how,this))return false}if(!is_new){var tmp=what.parents(".tree:eq(0)");if(tmp.get(0)!=this.container.get(0)){tmp=tree_component.inst[tmp.attr("id")];if(tmp.settings.languages.length){var res=[];if(this.settings.languages.length==0)res.push("."+tmp.current_lang);else{for(var i in this.settings.languages){if(typeof this.settings.languages[i]=="function")continue;for(var j in tmp.settings.languages){if(typeof tmp.settings.languages[j]=="function")continue;if(this.settings.languages[i]==tmp.settings.languages[j])res.push("."+this.settings.languages[i])}}}if(res.length==0)return this.error("MOVE: NO COMMON LANGUAGES");what.find("a").not(res.join(",")).remove()}what.find("a.clicked").removeClass("clicked ui-state-active")}}what=_what;switch(how){case"before":$where.parents("ul:eq(0)").children("li.last").removeClass("last");$where.parent().before(what.removeClass("last"));$where.parents("ul:eq(0)").children("li:last").addClass("last");break;case"after":$where.parents("ul:eq(0)").children("li.last").removeClass("last");$where.parent().after(what.removeClass("last"));$where.parents("ul:eq(0)").children("li:last").addClass("last");break;case"inside":if($where.parent().children("ul:first").size()){if(this.settings.rules.createat=="top")$where.parent().children("ul:first").prepend(what.removeClass("last")).children("li:last").addClass("last");else $where.parent().children("ul:first").children(".last").removeClass("last").end().append(what.removeClass("last")).children("li:last").addClass("last")}else{what.addClass("last");$where.parent().append("<ul/>").removeClass("leaf").addClass("closed");$where.parent().children("ul:first").prepend(what)}if($where.parent().hasClass("closed")){this.open_branch($where)}break;default:break}if($parent.find("li").size()==0){var $li=$parent.parent();$li.removeClass("open").removeClass("closed").addClass("leaf");if(!$li.is(".tree"))$li.children("ul").remove();$li.parents("ul:eq(0)").children("li.last").removeClass("last").end().children("li:last").addClass("last");this.set_cookie("open")}else{$parent.children("li.last").removeClass("last");$parent.children("li:last").addClass("last")}if(is_copy)this.settings.callback.oncopy.call(null,this.get_node(what).get(0),this.get_node(where).get(0),how,this,rb);else if(is_new)this.settings.callback.oncreate.call(null,this.get_node(what).get(0),($where.is("ul")?-1:this.get_node(where).get(0)),how,this,rb);else this.settings.callback.onmove.call(null,this.get_node(what).get(0),this.get_node(where).get(0),how,this,rb);return what},error:function(code){this.settings.callback.error.call(null,code,this);return false},lock:function(state){this.locked=state;if(this.locked)this.container.children("ul:eq(0)").addClass("locked");else this.container.children("ul:eq(0)").removeClass("locked")},cut:function(obj){if(this.locked)return this.error("LOCKED");obj=obj?this.get_node(obj):this.container.find("a.clicked").filter(":first-child").parent();if(!obj||!obj.size())return this.error("CUT: NO NODE SELECTED");this.copy_nodes=false;this.cut_nodes=obj},copy:function(obj){if(this.locked)return this.error("LOCKED");obj=obj?this.get_node(obj):this.container.find("a.clicked").filter(":first-child").parent();if(!obj||!obj.size())return this.error("COPY: NO NODE SELECTED");this.copy_nodes=obj;this.cut_nodes=false},paste:function(obj,position){if(this.locked)return this.error("LOCKED");var root=false;if(obj==-1){root=true;obj=this.container}else obj=obj?this.get_node(obj):this.selected;if(!root&&(!obj||!obj.size()))return this.error("PASTE: NO NODE SELECTED");if(!this.copy_nodes&&!this.cut_nodes)return this.error("PASTE: NOTHING TO DO");var _this=this;var pos=position;if(position=="before"){position=obj.parent().children().index(obj);obj=obj.parents("li:eq(0)")}else if(position=="after"){position=obj.parent().children().index(obj)+1;obj=obj.parents("li:eq(0)")}else if((typeof position).toLowerCase()=="undefined"||position=="inside"){position=(this.settings.rules.createat=="top")?0:obj.children("ul:eq(0)").children("li").size()}if(!root&&obj.size()==0){root=true;obj=this.container}if(this.copy_nodes&&this.copy_nodes.size()){var ok=true;if(!ok)return this.error("Invalid paste");if(!root&&!this.checkMove(this.copy_nodes,obj.children("a:eq(0)"),"inside"))return false;if(obj.children("ul").size()==0||(root==true&&obj.children("ul").children("li").size()==0)){if(!root)var a=this.moved(this.copy_nodes,obj.children("a:eq(0)"),"inside",false,true);else var a=this.moved(this.copy_nodes,this.container.children("ul:eq(0)"),"inside",false,true)}else if(pos=="before"&&obj.children("ul:eq(0)").children("li:nth-child("+(position+1)+")").size())var a=this.moved(this.copy_nodes,obj.children("ul:eq(0)").children("li:nth-child("+(position+1)+")").children("a:eq(0)"),"before",false,true);else if(pos=="after"&&obj.children("ul:eq(0)").children("li:nth-child("+(position)+")").size())var a=this.moved(this.copy_nodes,obj.children("ul:eq(0)").children("li:nth-child("+(position)+")").children("a:eq(0)"),"after",false,true);else if(obj.children("ul:eq(0)").children("li:nth-child("+(position+1)+")").size())var a=this.moved(this.copy_nodes,obj.children("ul:eq(0)").children("li:nth-child("+(position+1)+")").children("a:eq(0)"),"before",false,true);else var a=this.moved(this.copy_nodes,obj.children("ul:eq(0)").children("li:last").children("a:eq(0)"),"after",false,true);this.copy_nodes=false}if(this.cut_nodes&&this.cut_nodes.size()){var ok=true;obj.parents().andSelf().each(function(){if(_this.cut_nodes.index(this)!=-1){ok=false;return false}});if(!ok)return this.error("Invalid paste");if(!root&&!this.checkMove(this.cut_nodes,obj.children("a:eq(0)"),"inside"))return false;if(obj.children("ul").size()==0||(root==true&&obj.children("ul").children("li").size()==0)){if(!root)var a=this.moved(this.cut_nodes,obj.children("a:eq(0)"),"inside");else var a=this.moved(this.cut_nodes,this.container.children("ul:eq(0)"),"inside")}else if(pos=="before"&&obj.children("ul:eq(0)").children("li:nth-child("+(position+1)+")").size())var a=this.moved(this.cut_nodes,obj.children("ul:eq(0)").children("li:nth-child("+(position+1)+")").children("a:eq(0)"),"before");else if(pos=="after"&&obj.children("ul:eq(0)").children("li:nth-child("+(position)+")").size())var a=this.moved(this.cut_nodes,obj.children("ul:eq(0)").children("li:nth-child("+(position)+")").children("a:eq(0)"),"after");else if(obj.children("ul:eq(0)").children("li:nth-child("+(position+1)+")").size())var a=this.moved(this.cut_nodes,obj.children("ul:eq(0)").children("li:nth-child("+(position+1)+")").children("a:eq(0)"),"before");else var a=this.moved(this.cut_nodes,obj.children("ul:eq(0)").children("li:last").children("a:eq(0)"),"after");this.cut_nodes=false}},search:function(str){var _this=this;if(!str||(this.srch&&str!=this.srch)){this.srch="";this.srch_opn=false;this.container.find("a.search").removeClass("search ui-state-highlight")}this.srch=str;if(!str)return;if(this.settings.data.async){if(!this.srch_opn){var dd=$.extend({"search":str},this.settings.data.async_data(false,this));$.ajax({type:this.settings.data.method,url:this.settings.data.url,data:dd,dataType:"text",success:function(data){_this.srch_opn=$.unique(data.split(","));_this.search.apply(_this,[str])}})}else if(this.srch_opn.length){if(this.srch_opn&&this.srch_opn.length){var opn=false;for(var j=0;j<this.srch_opn.length;j++){if(this.get_node("#"+this.srch_opn[j]).size()>0){opn=true;var tmp="#"+this.srch_opn[j];delete this.srch_opn[j];this.open_branch(tmp,true,function(){_this.search.apply(_this,[str])})}}if(!opn){this.srch_opn=[];_this.search.apply(_this,[str])}}}else{var selector="a";if(this.settings.languages.length)selector+="."+this.current_lang;this.container.find(selector+":contains('"+str+"')").addClass(this.settings.ui.theme_name=="themeroller"?"search ui-state-highlight":"search");this.srch_opn=false}}else{var selector="a";if(this.settings.languages.length)selector+="."+this.current_lang;this.container.find(selector+":contains('"+str+"')").addClass(this.settings.ui.theme_name=="themeroller"?"search ui-state-highlight":"search").parents("li.closed").each(function(){_this.open_branch(this,true)})}},destroy:function(){this.hide_context();this.container.unbind(".jstree");$("#"+this.container.attr("id")).die("click.jstree").die("dblclick.jstree").die("contextmenu.jstree").die("mouseover.jstree").die("mouseout.jstree").die("mousedown.jstree");this.container.removeClass("tree ui-widget ui-widget-content tree-default tree-"+this.settings.ui.theme_name).children("ul").removeClass("no_dots rtl ltr locked").find("li").removeClass("leaf").removeClass("open").removeClass("closed").removeClass("last").children("a").removeClass("clicked hover search ui-state-active ui-state-hover ui-state-highlight ui-state-default");if(this.cntr==tree_component.focused){for(var i in tree_component.inst){if(i!=this.cntr&&i!=this.container.attr("id")){tree_component.inst[i].focus();break}}}tree_component.inst[this.cntr]=false;tree_component.inst[this.container.attr("id")]=false;delete tree_component.inst[this.cntr];delete tree_component.inst[this.container.attr("id")];tree_component.cntr--}}}})(jQuery);
;function get_css(rule_name, stylesheet, delete_flag) {
	if (!document.styleSheets) return false;
	rule_name = rule_name.toLowerCase(); stylesheet = stylesheet || 0;
	for (var i = stylesheet; i < document.styleSheets.length; i++) { 
		var styleSheet = document.styleSheets[i]; css_rules = document.styleSheets[i].cssRules || document.styleSheets[i].rules;
		if(!css_rules) continue;
		var j = 0;
		do {
			//if(i == 2 && j > 30) alert(css_rules[j].selectorText.toLowerCase() + " " + rule_name);
			if(css_rules.length && j > css_rules.length + 5) return false;
			if(css_rules[j].selectorText && css_rules[j].selectorText.toLowerCase() == rule_name) {
				if(delete_flag == true) {
					if(document.styleSheets[i].removeRule) document.styleSheets[i].removeRule(j);
					if(document.styleSheets[i].deleteRule) document.styleSheets[i].deleteRule(j);
					return true;
				}
				else return css_rules[j];
			}
		}
		while (css_rules[++j]);
	}
	return false;
}
function add_css(rule_name, stylesheet) {
	rule_name = rule_name.toLowerCase(); stylesheet = stylesheet || 0;
	if (!document.styleSheets || get_css(rule_name, stylesheet)) return false;
	(document.styleSheets[stylesheet].insertRule) ? document.styleSheets[stylesheet].insertRule(rule_name+' { }', 0) : document.styleSheets[stylesheet].addRule(rule_name, null, 0);
	return get_css(rule_name, stylesheet);
}
function get_sheet_num (href_name) {
	if (!document.styleSheets) return false;
	for (var i = 0; i < document.styleSheets.length; i++) { if(document.styleSheets[i].href && document.styleSheets[i].href.toString().match(href_name)) return i; } 
	return false;
}
function remove_css(rule_name, stylesheet) { return get_css(rule_name, stylesheet, true); }

function add_sheet(url, media) {
	if(document.createStyleSheet) {
		document.createStyleSheet(url);
	}
	else {
		var newSS	= document.createElement('link');
		newSS.rel	= 'stylesheet';
		newSS.type	= 'text/css';
		newSS.media	= media || "all";

		newSS.href	= url;
		// var styles	= "@import url(' " + url + " ');";
		// newSS.href	='data:text/css,'+escape(styles);
		document.getElementsByTagName("head")[0].appendChild(newSS);
	}
};eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(5($){8 g=[],B=[],G=12=k,3c=$(\'<E Y="1g-E 3x" 24="1Z:3h;1i:0;1w:0;2r:2X;"><E Y="3G"></E><E Y="3E"></E><E Y="3C"></E></E>\')[0],2L=$(\'<29 Y="1g-29 3q"></29>\')[0],2H=$(\'<X 24="1Z:3j;"><E Y="1g-1k"></E></X>\')[0],3g=$(\'<3Q Y="1g-1k-3P" />\')[0],$1V=$(\'<E 3N="3M-1g-E" 24="1Z:3h;1i:0;1w:0;"></E>\'),1P={34:31,2Y:31,2p:0,2V:0,2U:0,1J:0,2B:k,2i:k,1r:k,2d:k,2M:Q,2J:Q};$(5(){$1V.3s(\'3p\')});$.R({25:5(a){2.11=[];2.1s(a)}});$.R($.25,{1X:{1s:5(a){4(a&&a.9){J(8 i=0;i<a.9;i++){2.2y(a[i]);a[i].V=2}}},2y:5(a){4(a 2x $.C)2.11.16(a);a.V=2;8 b=2;$(a.q).1T(5(){4(a.M)s;J(8 i=0;i<b.11.9;i++){4(b.11[i].M){b.11[i].W();a.14();s}}},5(){})}}});$.R({C:5(a,b,c){2.l=[];2.1c=[];2.M=Q;2.O=Q;2.N=k;2.u=$.R({},1P,c);2.q=a;2.$w=k;2.$1q=k;2.17=k;2.V=k;2.1L=k;2.1s();4(b&&b.2T==2S)2.2Q(b)}});$.R($.C,{2m:5(e){8 t=e.q;4(B.9&&t==B[0].q)s;1y(t.2t&&t.2t!=$1V[0])t=t.2t;4(!$(B).3B(5(){s 2.$w[0]==t}).9){$.C.1n()}},2f:5(e){3A(e.2c){1m 13:4(12)12.1a(e,12.$p[0]);1h;1m 27:$.C.1n();1h;1m 37:4(!G)G=B[0];8 a=G;4(a&&a.N){8 b=a.N;b.$p.K(\'1F\').K(\'1E\');a.W();b.15(P);2l(5(){b.23()})}v 4(a&&a.V){8 c,F=a.V.11;4((c=$.18(a,F))>-1){4(--c<0)c=F.9-1;$.C.1n();F[c].14();F[c].Z();4(F[c].l.9)F[c].l[0].15(P)}}1h;1m 38:4(G)G.1Y(-1);1h;1m 39:4(!G)G=B[0];8 m,a=G,1t=12?12.x:k;4(a){4(1t&&1t.l.9){1t.14();1t.l[0].15()}v 4((a=a.2C())){8 c,F=a.V.11;4((c=$.18(a,F))>-1){4(++c>=F.9)c=0;$.C.1n();F[c].14();F[c].Z();4(F[c].l.9)F[c].l[0].15(P)}}}1h;1m 3i:4(!G){4(B.9&&B[0].l.9)B[0].l[0].15()}v G.1Y();1h}4(e.2c>36&&e.2c<3S)s Q},1n:5(){1y(B.9)B[0].W()},3R:5(d){$.R(1P,d)},1X:{1s:5(){8 a=2;4(!2.q)s;v 4(2.q 2x $.1j){2.N=2.q;2.q.3f(2);2.q=2.q.$p}g.16(2);2.$w=$(3c.1A(1));2.$1q=$(2L.1A(1));2.$w[0].1z(2.$1q[0]);$1V[0].1z(2.$w[0]);4(!2.N){$(2.q).1a(5(e){a.1r(e)}).1T(5(e){a.Z();4(a.u.2p){a.1L=2l(5(){4(!a.M)a.1r(e)},a.u.2p)}},5(){4(!a.M)$(2).1W(\'2w\');4(a.1L)3e(a.1L)})}v{2.$w.1T(5(){a.Z()},5(){})}},Z:5(){4(!2.N)$(2.q).10(\'2w\');v 2.O=P},2v:5(a){4(a 2x $.1j){4($.18(a,2.l)==-1){2.$1q.3O(a.$p);2.l.16(a);a.A=2;4(a.x)2.1c.16(a.x)}}v{2.2v(19 $.1j(a,2.u))}},2Q:5(a){J(8 i=0;i<a.9;i++){2.2v(a[i])}},3d:5(a){8 b=$.18(a,2.l);4(b>-1)2.l.1U(b,1);a.A=k},W:5(){4(!2.M)s;8 i,I=$.18(2,B);2.$w.W();4(I>=0)B.1U(I,1);2.M=2.O=Q;$(2.q).1W(\'2w\');J(i=0;i<2.1c.9;i++){2.1c[i].W()}J(i=0;i<2.l.9;i++){4(2.l[i].O)2.l[i].1S()}4(!B.9)$(2u).K(\'3b\',$.C.2m).K(\'3a\',$.C.2f);4(G==2)G=k;4(2.u.2i)2.u.2i.1R(2)},14:5(e){4(2.M)s;8 a,D=2.N;4(2.l.9){4(D){a=35(D.A.$w.1e(\'z-33\'));2.$w.1e(\'z-33\',(3L(a)?1:a+1))}2.$w.1e({32:\'3K\',2r:\'3J\'});4(2.u.1J){4(2.$w.L()<2.u.1J)2.$w.1e(\'L\',2.u.1J)}2.30();2.$w.1e({2r:\'2X\',32:\'\'}).14();4($.2Z.3I)2.$1q.1e(\'L\',35($.2Z.3H)==6?2.$w.L()-7:2.$1q.L());4(2.u.2B)2.u.2B.1R(2)}4(B.9==0)$(2u).2W(\'3b\',$.C.2m).2W(\'3a\',$.C.2f);2.M=P;B.16(2)},30:5(){8 a,o,S,H,1N,1b,1o,2o=$(1K).L(),1p=$(1K).T(),D=2.N,T=2.$w[0].2R,L=2.$w[0].3F,1M;4(D){o=D.$p.2q();S=o.1w+D.$p.L();H=o.1i}v{a=$(2.q);o=a.2q();S=o.1w+2.u.2U;H=o.1i+a.T()+2.u.2V}4($.2s.2P){1b=$(1K).2P();4(1p<T){H=1b}v 4(1p+1b<H+T){4(D){1N=D.A.$w.2q();1M=D.A.$w[0].2R;4(T<=1M){H=1N.1i+1M-T}v{H=1N.1i}4(1p+1b<H+T){H-=H+T-(1p+1b)}}v{H-=H+T-(1p+1b)}}}4($.2s.2O){1o=$(1K).2O();4(2o+1o<S+L){4(D){S-=D.$p.L()+L;4(S<1o)S=1o}v{S-=S+L-(2o+1o)}}}2.$w.1e({1w:S,1i:H})},1r:5(e){4(2.M){2.W();2.Z()}v{$.C.1n();2.14(e)}},2k:5(a,b){8 c=2;2.17=2l(5(){a.1R(c);c.17=k},b)},1d:5(){4(2.17){3e(2.17);2.17=k}},1Y:5(a){8 i,I=0,1O=2.l.9,o=a||1;J(i=0;i<1O;i++){4(2.l[i].O){I=i;1h}}2.l[I].2j();3D{I+=o;4(I>=1O)I=0;v 4(I<0)I=1O-1}1y(2.l[I].1Q);2.l[I].15(P)},2C:5(){8 m=2;1y(m.N)m=m.N.A;s m.V?m:k},1I:5(){8 a,1k;2.W();4(!2.N)$(2.q).K(\'1a\').K(\'1E\').K(\'1F\');v 2.$w.K(\'1E\').K(\'1F\');1y(2.l.9){1k=2.l[0];1k.1I();2N 1k}4((a=$.18(2,g))>-1)g.1U(a,1);4(2.V){4((a=$.18(2,2.V.11))>-1)2.V.11.1U(a,1)}2.$w.2h()}}});$.R({1j:5(a,b){4(2g a==\'2e\')a={y:a};2.y=a.y||\'\';2.1D=a.1D||k;2.20=a.q||k;2.10=a.10||k;2.1B=a.1B||k;2.$p=k;2.A=k;2.x=k;2.u=$.R({},1P,b);2.O=Q;2.1l=P;2.1Q=Q;2.1s();4(a.x)19 $.C(2,a.x,b)}});$.R($.1j,{1X:{1s:5(){8 i,1C,y=2.y,2b=2;2.$p=$(2H.1A(1));4(2.10)2.$p[0].2K(\'Y\',2.10);4(2.u.2M&&2.1B)2.$p[0].3z=2.1B;4(y==\'\'){2.$p.10(\'1g-1Q\');2.1Q=P}v{1C=2g y==\'2e\';4(1C&&2.1D)y=$(\'<a 3y="\'+2.1D+\'"\'+(2.20?\'q="\'+2.20+\'"\':\'\')+\'>\'+y+\'</a>\');v 4(1C||!y.9)y=[y];J(i=0;i<y.9;i++){4(2g y[i]==\'2e\'){2a=2u.3w(\'3v\');2a.3t=y[i];2.$p[0].1u.1z(2a)}v 2.$p[0].1u.1z(y[i].1A(1))}}2.$p.1a(5(e){2b.1a(e,2)});2.23()},1a:5(e,a){4(2.1l&&2.u.1r)2.u.1r.1R(a,e,2)},23:5(){8 a=2;2.$p.1T(5(){a.15()},5(){a.2j()})},15:5(a){2.1d();8 i,1f=2.A.1c,D=2.A.l,2b=2;4(2.A.17)2.A.1d();4(!2.1l)s;J(i=0;i<D.9;i++){4(D[i].O)D[i].1S()}2.Z();G=2.A;J(i=0;i<1f.9;i++){4(1f[i].M&&1f[i]!=2.x&&!1f[i].17)1f[i].2k(5(){2.W()},1f[i].u.2Y)}4(2.x&&!a){2.x.2k(5(){2.14()},2.x.u.34)}},2j:5(){2.1d();4(!2.1l)s;4(!2.x||!2.x.M)2.1S()},1d:5(){4(2.x){2.x.1d()}},Z:5(){2.O=P;2.$p.10(\'O\');8 a=2.A.N;4(a&&!a.O)a.Z();12=2},1S:5(){2.O=Q;2.$p.1W(\'O\');4(2==12)12=k},3r:5(){2.$p.1W(\'2I\');2.1l=P},3u:5(){2.$p.10(\'2I\');2.1l=Q},1I:5(){2.1d();2.$p.2h();2.$p.K(\'1E\').K(\'1F\').K(\'1a\');4(2.x){2.x.1I();2N 2.x}2.A.3d(2)},3f:5(b){4(2.x)s;2.x=b;4(2.A&&$.18(b,2.A.1c)==-1)2.A.1c.16(b);4(2.u.2d){8 a=3g.1A(0);a.2K(\'y\',2.u.2d);2.$p[0].1u.1z(a)}}}});$.R($.2s,{28:5(c,d,e){8 f=5(a){8 b=[],1v,1G,U,$X,i,1H,3o,q,26=k;U=j(a,\'2G\');J(i=0;i<U.9;i++){1v=[];4(!U[i].1x.9){b.16(19 $.1j(\'\',c));3n}4((1H=h(U[i],\'2n\'))){1v=f(1H);$(1H).2h()}$X=$(U[i]);4($X[0].1x.9==1&&$X[0].1x[0].22==3)q=$X[0].1x[0].3m;v q=$X[0].1x;4(c&&c.2J)26=$X.3l(\'Y\');1G=19 $.1j({y:q,10:26},c);b.16(1G);4(1v.9)19 $.C(1G,1v,c)}s b};s 2.2z(5(){8 a,m;4(d||(a=h(2,\'2n\'))){a=d?$(d).3k(P)[0]:a;l=f(a);4(l.9){m=19 $.C(2,l,c);4(e)e.2y(m)}$(a).W()}})},2F:5(a){s 2.2z(5(){8 i,U=j(2,\'2G\');4(U.9){2E=19 $.25();J(i=0;i<U.9;i++)$(U[i]).28(a,k,2E)}})},1g:5(a,b){s 2.2z(5(){4(b&&b.2T==2S)19 $.C(2,b,a);v{4(2.21.2A()==\'2n\')$(2).2F(a);v $(2).28(a,b)}})}});8 h=5(a,b){4(!a)s k;8 n=a.1u;J(;n;n=n.2D){4(n.22==1&&n.21.2A()==b)s n}s k};8 j=5(a,b){4(!a)s[];8 r=[],n=a.1u;J(;n;n=n.2D){4(n.22==1&&n.21.2A()==b)r[r.9]=n}s r}})(3T);',62,242,'||this||if|function|||var|length|||||||||||null|menuItems||||eLI|target||return||settings|else|eDIV|subMenu|src||parentMenu|visibleMenus|Menu|pmi|div|mcm|activeMenu|posY|pos|for|unbind|width|visible|parentMenuItem|active|true|false|extend|posX|height|lis|menuCollection|hide|li|class|setActive|addClass|menus|activeItem||show|hoverIn|push|timer|inArray|new|click|wst|subMenus|removeTimer|css|pms|menu|break|top|MenuItem|item|enabled|case|closeAll|wsl|wh|eUL|onClick|init|asm|firstChild|subItems|left|childNodes|while|appendChild|cloneNode|data|isStr|url|mouseover|mouseout|menuItem|subUL|destroy|minWidth|window|openTimer|pheight|pmo|mil|defaults|separator|call|setInactive|hover|splice|rootDiv|removeClass|prototype|selectNextItem|position|urlTarget|nodeName|nodeType|bindHover|style|MenuCollection|classNames||menuFromElement|ul|elem|self|keyCode|arrowSrc|string|checkKey|typeof|remove|onClose|hoverOut|addTimer|setTimeout|checkMouse|UL|ww|hoverOpenDelay|offset|display|fn|parentNode|document|addItem|activetarget|instanceof|addMenu|each|toUpperCase|onOpen|inMenuCollection|nextSibling|bar|menuBarFromUL|LI|menuItemElement|disabled|copyClassAttr|setAttribute|menuULElement|addExpando|delete|scrollLeft|scrollTop|addItems|clientHeight|Array|constructor|offsetLeft|offsetTop|bind|none|hideDelay|browser|setPosition|200|visibility|index|showDelay|parseInt|||||keydown|mousedown|menuDIVElement|removeItem|clearTimeout|addSubMenu|arrowElement|absolute|40|relative|clone|attr|nodeValue|continue|submenu|body|innerbox|enable|appendTo|innerHTML|disable|span|createElement|outerbox|href|menuData|switch|filter|shadowbox3|do|shadowbox2|clientWidth|shadowbox1|version|msie|block|hidden|isNaN|root|id|append|arrow|img|setDefaults|41|jQuery'.split('|'),0,{}));
;/* Copyright (c) 2007 Paul Bakaus (paul.bakaus@googlemail.com) and Brandon Aaron (brandon.aaron@gmail.com || http://brandonaaron.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * $LastChangedDate: 2007-12-20 08:43:48 -0600 (Thu, 20 Dec 2007) $
 * $Rev: 4257 $
 *
 * Version: 1.2
 *
 * Requires: jQuery 1.2+
 */
(function($){$.dimensions={version:'1.2'};$.each(['Height','Width'],function(i,name){$.fn['inner'+name]=function(){if(!this[0])return;var torl=name=='Height'?'Top':'Left',borr=name=='Height'?'Bottom':'Right';return this.is(':visible')?this[0]['client'+name]:num(this,name.toLowerCase())+num(this,'padding'+torl)+num(this,'padding'+borr);};$.fn['outer'+name]=function(options){if(!this[0])return;var torl=name=='Height'?'Top':'Left',borr=name=='Height'?'Bottom':'Right';options=$.extend({margin:false},options||{});var val=this.is(':visible')?this[0]['offset'+name]:num(this,name.toLowerCase())+num(this,'border'+torl+'Width')+num(this,'border'+borr+'Width')+num(this,'padding'+torl)+num(this,'padding'+borr);return val+(options.margin?(num(this,'margin'+torl)+num(this,'margin'+borr)):0);};});$.each(['Left','Top'],function(i,name){$.fn['scroll'+name]=function(val){if(!this[0])return;return val!=undefined?this.each(function(){this==window||this==document?window.scrollTo(name=='Left'?val:$(window)['scrollLeft'](),name=='Top'?val:$(window)['scrollTop']()):this['scroll'+name]=val;}):this[0]==window||this[0]==document?self[(name=='Left'?'pageXOffset':'pageYOffset')]||$.boxModel&&document.documentElement['scroll'+name]||document.body['scroll'+name]:this[0]['scroll'+name];};});$.fn.extend({position:function(){var left=0,top=0,elem=this[0],offset,parentOffset,offsetParent,results;if(elem){offsetParent=this.offsetParent();offset=this.offset();parentOffset=offsetParent.offset();offset.top-=num(elem,'marginTop');offset.left-=num(elem,'marginLeft');parentOffset.top+=num(offsetParent,'borderTopWidth');parentOffset.left+=num(offsetParent,'borderLeftWidth');results={top:offset.top-parentOffset.top,left:offset.left-parentOffset.left};}return results;},offsetParent:function(){var offsetParent=this[0].offsetParent;while(offsetParent&&(!/^body|html$/i.test(offsetParent.tagName)&&$.css(offsetParent,'position')=='static'))offsetParent=offsetParent.offsetParent;return $(offsetParent);}});function num(el,prop){return parseInt($.curCSS(el.jquery?el[0]:el,prop,true))||0;};})(jQuery);;/**
*    Chained Selects for jQuery 
*    Copyright (C) 2008 Ziadin Givan www.CodeAssembly.com  
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see http://www.gnu.org/licenses/
*
*    
*   settings = { usePost : true, before:function() {}, after: function() {}, default: null, parameters : { parameter1 : 'value1', parameter2 : 'value2'} }	
*   if usePost is true, then the form will use POST to pass the parameters to the target, otherwise will use GET
*   "before" function is called before the ajax request and "after" function is called after the ajax request.
*   If defaultValue is not null then the specified option will be selected.
*   You can specify additional parameters to be sent to the the server in settings.parameters.
*
*/
;(function($) {
    
jQuery.fn.chainSelect = function( target, url, settings ) 
{
  return this.each( function()
  {
	$(this).change( function( ) 
	{
		settings = $.extend(
		{
			after : null,
			before : null,
			usePost : false,
			defaultValue : null,
			parameters : {'_id' : $(this).attr('id'), '_name' : $(this).attr('name')}
        } , settings);

		settings.parameters._value =  $(this).val();

		if (settings.before != null) 
		{
			settings.before( target );
		}

		ajaxCallback = function(data, textStatus) 
		{
			$(target).html("");//clear old options
			data = eval(data);//get json array
                        if ( data != null ) {
			    for (i = 0; i < data.length; i++) {
				$(target).get(0).add(new Option(data[i].name, data[i].value), document.all ? i : null);
			    }
			} else {
				$(target).get(0).add(new Option('- select a country -', 0), document.all ? i : null);
			}

			if (settings.defaultValue != null)
			{
				$(target).val(settings.defaultValue);//select default value
			} else
			{
				$("option:first", target).attr( "selected", "selected" );//select first option
			}

			if (settings.after != null) 
			{
				settings.after(target);
			}

			$(target).change();//call next chain
		};

		if (settings.usePost == true)
		{
			$.post( url, settings.parameters, ajaxCallback );
		} else
		{
			$.get( url, settings.parameters, ajaxCallback );
		}
	});
  });
};

})(jQuery);;/*
 * Treeview pre-1.4.1 - jQuery plugin to hide and show branches of a tree
 * 
 * http://bassistance.de/jquery-plugins/jquery-plugin-treeview/
 * http://docs.jquery.com/Plugins/Treeview
 *
 * Copyright (c) 2007 Jörn Zaefferer
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Revision: $Id: jquery.treeview.js 4684 2008-02-07 19:08:06Z joern.zaefferer $
 *
 */;(function($){$.extend($.fn,{swapClass:function(c1,c2){var c1Elements=this.filter('.'+c1);this.filter('.'+c2).removeClass(c2).addClass(c1);c1Elements.removeClass(c1).addClass(c2);return this;},replaceClass:function(c1,c2){return this.filter('.'+c1).removeClass(c1).addClass(c2).end();},hoverClass:function(className){className=className||"hover";return this.hover(function(){$(this).addClass(className);},function(){$(this).removeClass(className);});},heightToggle:function(animated,callback){animated?this.animate({height:"toggle"},animated,callback):this.each(function(){jQuery(this)[jQuery(this).is(":hidden")?"show":"hide"]();if(callback)callback.apply(this,arguments);});},heightHide:function(animated,callback){if(animated){this.animate({height:"hide"},animated,callback);}else{this.hide();if(callback)this.each(callback);}},prepareBranches:function(settings){if(!settings.prerendered){this.filter(":last-child:not(ul)").addClass(CLASSES.last);this.filter((settings.collapsed?"":"."+CLASSES.closed)+":not(."+CLASSES.open+")").find(">ul").hide();}return this.filter(":has(>ul)");},applyClasses:function(settings,toggler){this.filter(":has(>ul):not(:has(>a))").find(">span").click(function(event){if(this==event.target)toggler.apply($(this).next());}).add($("a",this)).hoverClass();if(!settings.prerendered){this.filter(":has(>ul:hidden)").addClass(CLASSES.expandable).replaceClass(CLASSES.last,CLASSES.lastExpandable);this.not(":has(>ul:hidden)").addClass(CLASSES.collapsable).replaceClass(CLASSES.last,CLASSES.lastCollapsable);this.prepend("<div class=\""+CLASSES.hitarea+"\"/>").find("div."+CLASSES.hitarea).each(function(){var classes="";$.each($(this).parent().attr("class").split(" "),function(){classes+=this+"-hitarea ";});$(this).addClass(classes);});}this.find("div."+CLASSES.hitarea).click(toggler);},treeview:function(settings){settings=$.extend({cookieId:"treeview"},settings);if(settings.add){return this.trigger("add",[settings.add]);}if(settings.toggle){var callback=settings.toggle;settings.toggle=function(){return callback.apply($(this).parent()[0],arguments);};}function treeController(tree,control){function handler(filter){return function(){toggler.apply($("div."+CLASSES.hitarea,tree).filter(function(){return filter?$(this).parent("."+filter).length:true;}));return false;};}$("a:eq(0)",control).click(handler(CLASSES.collapsable));$("a:eq(1)",control).click(handler(CLASSES.expandable));$("a:eq(2)",control).click(handler());}function toggler(){$(this).parent().find(">.hitarea").swapClass(CLASSES.collapsableHitarea,CLASSES.expandableHitarea).swapClass(CLASSES.lastCollapsableHitarea,CLASSES.lastExpandableHitarea).end().swapClass(CLASSES.collapsable,CLASSES.expandable).swapClass(CLASSES.lastCollapsable,CLASSES.lastExpandable).find(">ul").heightToggle(settings.animated,settings.toggle);if(settings.unique){$(this).parent().siblings().find(">.hitarea").replaceClass(CLASSES.collapsableHitarea,CLASSES.expandableHitarea).replaceClass(CLASSES.lastCollapsableHitarea,CLASSES.lastExpandableHitarea).end().replaceClass(CLASSES.collapsable,CLASSES.expandable).replaceClass(CLASSES.lastCollapsable,CLASSES.lastExpandable).find(">ul").heightHide(settings.animated,settings.toggle);}}function serialize(){function binary(arg){return arg?1:0;}var data=[];branches.each(function(i,e){data[i]=$(e).is(":has(>ul:visible)")?1:0;});$.cookie(settings.cookieId,data.join(""));}function deserialize(){var stored=$.cookie(settings.cookieId);if(stored){var data=stored.split("");branches.each(function(i,e){$(e).find(">ul")[parseInt(data[i])?"show":"hide"]();});}}this.addClass("treeview");var branches=this.find("li").prepareBranches(settings);switch(settings.persist){case"cookie":var toggleCallback=settings.toggle;settings.toggle=function(){serialize();if(toggleCallback){toggleCallback.apply(this,arguments);}};deserialize();break;case"location":var current=this.find("a").filter(function(){return this.href.toLowerCase()==location.href.toLowerCase();});if(current.length){current.addClass("selected").parents("ul, li").add(current.next()).show();}break;}branches.applyClasses(settings,toggler);if(settings.control){treeController(this,settings.control);$(settings.control).show();}return this.bind("add",function(event,branches){$(branches).prev().removeClass(CLASSES.last).removeClass(CLASSES.lastCollapsable).removeClass(CLASSES.lastExpandable).find(">.hitarea").removeClass(CLASSES.lastCollapsableHitarea).removeClass(CLASSES.lastExpandableHitarea);$(branches).find("li").andSelf().prepareBranches(settings).applyClasses(settings,toggler);});}});var CLASSES=$.fn.treeview.classes={open:"open",closed:"closed",expandable:"expandable",expandableHitarea:"expandable-hitarea",lastExpandableHitarea:"lastExpandable-hitarea",collapsable:"collapsable",collapsableHitarea:"collapsable-hitarea",lastCollapsableHitarea:"lastCollapsable-hitarea",lastCollapsable:"lastCollapsable",lastExpandable:"lastExpandable",last:"last",hitarea:"hitarea"};$.fn.Treeview=$.fn.treeview;})(jQuery);;/* Copyright (c) 2006 Brandon Aaron (http://brandonaaron.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) 
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * $LastChangedDate: 2007-07-21 18:44:59 -0500 (Sat, 21 Jul 2007) $
 * $Rev: 2446 $
 *
 * Version 2.1.1
 */
eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(b($){$.m.E=$.m.g=b(s){h($.x.10&&/6.0/.I(D.B)){s=$.w({c:\'3\',5:\'3\',8:\'3\',d:\'3\',k:M,e:\'F:i;\'},s||{});C a=b(n){f n&&n.t==r?n+\'4\':n},p=\'<o Y="g"W="0"R="-1"e="\'+s.e+\'"\'+\'Q="P:O;N:L;z-H:-1;\'+(s.k!==i?\'G:J(K=\\\'0\\\');\':\'\')+\'c:\'+(s.c==\'3\'?\'7(((l(2.9.j.A)||0)*-1)+\\\'4\\\')\':a(s.c))+\';\'+\'5:\'+(s.5==\'3\'?\'7(((l(2.9.j.y)||0)*-1)+\\\'4\\\')\':a(s.5))+\';\'+\'8:\'+(s.8==\'3\'?\'7(2.9.S+\\\'4\\\')\':a(s.8))+\';\'+\'d:\'+(s.d==\'3\'?\'7(2.9.v+\\\'4\\\')\':a(s.d))+\';\'+\'"/>\';f 2.T(b(){h($(\'> o.g\',2).U==0)2.V(q.X(p),2.u)})}f 2}})(Z);',62,63,'||this|auto|px|left||expression|width|parentNode||function|top|height|src|return|bgiframe|if|false|currentStyle|opacity|parseInt|fn||iframe|html|document|Number||constructor|firstChild|offsetHeight|extend|browser|borderLeftWidth||borderTopWidth|userAgent|var|navigator|bgIframe|javascript|filter|index|test|Alpha|Opacity|absolute|true|position|block|display|style|tabindex|offsetWidth|each|length|insertBefore|frameborder|createElement|class|jQuery|msie'.split('|'),0,{}));// jQuery Context Menu Plugin
//
// Version 1.00
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
//
// Visit http://abeautifulsite.net/notebook/80 for usage and more information
//
// Terms of Use
//
// This software is licensed under a Creative Commons License and is copyrighted
// (C)2008 by Cory S.N. LaViska.
//
// For details, visit http://creativecommons.org/licenses/by/3.0/us/
//
;(function($) {
	$.fn.extend({
		
		contextMenu: function(o, callback) {
			// Defaults
			if( o.menu == undefined ) return false;
			if( o.inSpeed == undefined ) o.inSpeed = 150;
			if( o.outSpeed == undefined ) o.outSpeed = 75;
			// 0 needs to be -1 for expected results (no fade)
			if( o.inSpeed == 0 ) o.inSpeed = -1;
			if( o.outSpeed == 0 ) o.outSpeed = -1;
			// Loop each context menu
			$(this).each( function() {
				var el = $(this);
				var offset = $(el).offset();
				// Add contextMenu class
				$('#' + o.menu).addClass('contextMenu');
				// Simulate a true right click
				$(this).mousedown( function(e) {
					var evt = e;
					$(this).mouseup( function(e) {
						var srcElement = $(this);
						$(this).unbind('mouseup');
						if( evt.button == 2 ) {
							// Hide context menus that may be showing
							$(".contextMenu").hide();
							// Get this context menu
							var menu = $('#' + o.menu);
							
							if( $(el).hasClass('disabled') ) return false;
							
							// Detect mouse position
							var d = {}, x, y;
							if( self.innerHeight ) {
								d.pageYOffset = self.pageYOffset;
								d.pageXOffset = self.pageXOffset;
								d.innerHeight = self.innerHeight;
								d.innerWidth = self.innerWidth;
							} else if( document.documentElement &&
								document.documentElement.clientHeight ) {
								d.pageYOffset = document.documentElement.scrollTop;
								d.pageXOffset = document.documentElement.scrollLeft;
								d.innerHeight = document.documentElement.clientHeight;
								d.innerWidth = document.documentElement.clientWidth;
							} else if( document.body ) {
								d.pageYOffset = document.body.scrollTop;
								d.pageXOffset = document.body.scrollLeft;
								d.innerHeight = document.body.clientHeight;
								d.innerWidth = document.body.clientWidth;
							}
							(e.pageX) ? x = e.pageX : x = e.clientX + d.scrollLeft;
							(e.pageY) ? y = e.pageY : x = e.clientY + d.scrollTop;
							
							// Show the menu
							$(document).unbind('click');
							$(menu).css({ top: y, left: x }).fadeIn(o.inSpeed);
							// Hover events
							$(menu).find('A').mouseover( function() {
								$(menu).find('LI.hover').removeClass('hover');
								$(this).parent().addClass('hover');
							}).mouseout( function() {
								$(menu).find('LI.hover').removeClass('hover');
							});
							
							// Keyboard
							$(document).keypress( function(e) {
								switch( e.keyCode ) {
									case 38: // up
										if( $(menu).find('LI.hover').size() == 0 ) {
											$(menu).find('LI:last').addClass('hover');
										} else {
											$(menu).find('LI.hover').removeClass('hover').prevAll('LI:not(.disabled)').eq(0).addClass('hover');
											if( $(menu).find('LI.hover').size() == 0 ) $(menu).find('LI:last').addClass('hover');
										}
									break;
									case 40: // down
										if( $(menu).find('LI.hover').size() == 0 ) {
											$(menu).find('LI:first').addClass('hover');
										} else {
											$(menu).find('LI.hover').removeClass('hover').nextAll('LI:not(.disabled)').eq(0).addClass('hover');
											if( $(menu).find('LI.hover').size() == 0 ) $(menu).find('LI:first').addClass('hover');
										}
									break;
									case 13: // enter
										$(menu).find('LI.hover A').trigger('click');
									break;
									case 27: // esc
										$(document).trigger('click');
									break
								}
							});
							
							// When items are selected
							$('#' + o.menu).find('A').unbind('click');
							$('#' + o.menu).find('LI:not(.disabled) A').click( function() {
								$(document).unbind('click').unbind('keypress');
								$(".contextMenu").hide();
								// Callback
								if( callback ) callback( $(this).attr('href').substr(1), $(srcElement), {x: x - offset.left, y: y - offset.top, docX: x, docY: y} );
								return false;
							});
							
							// Hide bindings
							setTimeout( function() { // Delay for Mozilla
								$(document).click( function() {
									$(document).unbind('click').unbind('keypress');
									$(menu).fadeOut(o.outSpeed);
									return false;
								});
							}, 0);
						}
					});
				});
				
				// Disable text selection
				if( $.browser.mozilla ) {
					$('#' + o.menu).each( function() { $(this).css({ 'MozUserSelect' : 'none' }); });
				} else if( $.browser.msie ) {
					$('#' + o.menu).each( function() { $(this).bind('selectstart.disableTextSelect', function() { return false; }); });
				} else {
					$('#' + o.menu).each(function() { $(this).bind('mousedown.disableTextSelect', function() { return false; }); });
				}
				// Disable browser context menu (requires both selectors to work in IE/Safari + FF/Chrome)
				$(el).add('UL.contextMenu').bind('contextmenu', function() { return false; });
				
			});
			return $(this);
		},
		
		// Disable context menu items on the fly
		disableContextMenuItems: function(o) {
			if( o == undefined ) {
				// Disable all
				$(this).find('LI').addClass('disabled');
				return( $(this) );
			}
			$(this).each( function() {
				if( o != undefined ) {
					var d = o.split(',');
					for( var i = 0; i < d.length; i++ ) {
						$(this).find('A[href="' + d[i] + '"]').parent().addClass('disabled');
						
					}
				}
			});
			return( $(this) );
		},
		
		// Enable context menu items on the fly
		enableContextMenuItems: function(o) {
			if( o == undefined ) {
				// Enable all
				$(this).find('LI.disabled').removeClass('disabled');
				return( $(this) );
			}
			$(this).each( function() {
				if( o != undefined ) {
					var d = o.split(',');
					for( var i = 0; i < d.length; i++ ) {
						$(this).find('A[href="' + d[i] + '"]').parent().removeClass('disabled');
						
					}
				}
			});
			return( $(this) );
		},
		
		// Disable context menu(s)
		disableContextMenu: function() {
			$(this).each( function() {
				$(this).addClass('disabled');
			});
			return( $(this) );
		},
		
		// Enable context menu(s)
		enableContextMenu: function() {
			$(this).each( function() {
				$(this).removeClass('disabled');
			});
			return( $(this) );
		},
		
		// Destroy context menu(s)
		destroyContextMenu: function() {
			// Destroy specified context menus
			$(this).each( function() {
				// Disable action
				$(this).unbind('mousedown').unbind('mouseup');
			});
			return( $(this) );
		}
		
	});
})(jQuery);;;(function($) {

// Keep track of all header cells.
var cells = [];

// Attach to all headers.
$(document).ready(function() {
  $('table thead.sticky').each(function () {
    // Make all absolute positioned elements relative to the table.
    var height = $(this).parent('table').css('position', 'relative').height();
    
    // Find all header cells.
    $('th', this).each(function () {
      
      // Ensure each cell has an element in it.
      var html = $(this).html();
      if (html == ' ') {
	html = '&nbsp;';
      }
      if ($(this).children().size() == 0) {
	html = '<span>'+ html +'</span>';
      }
      
      // Clone and wrap cell contents in sticky wrapper that overlaps the cell's padding.
      $('<div class="sticky-header" style="position: fixed; display: none; top: 0px;">'+ html +'</div>').prependTo(this);
      var div = $('div.sticky-header', this).css({
	  'marginLeft': '-'+ $(this).css('paddingLeft'),
          'marginRight': '-'+ $(this).css('paddingRight'),
          'paddingLeft': $(this).css('paddingLeft'),
          'paddingTop': $(this).css('paddingTop'),
          'paddingRight': $(this).css('paddingRight'),
          'paddingBottom': $(this).css('paddingBottom')
	})[0];
      // Adjust width to fit cell and hide.
      $(div).css('paddingRight', parseInt($(div).css('paddingRight')) + $(this).width() - $(div).width() +'px');
      cells.push(div);
      
      // Get position.
      div.cell = this;
      div.table = $(this).parent('table')[0];
      div.stickyMax = height;
      div.stickyPosition = $(this).y();
    });
  });
});

// Track scrolling.
var scroll = function() {
  $(cells).each(function () {
    // Fetch scrolling position.
    var scroll = document.documentElement.scrollTop || document.body.scrollTop;
    var offset = scroll - this.stickyPosition - 4;
    if (offset > 0 && offset < this.stickyMax - 100) {
      $(this).css({display:'block'});
    }
    else {
      $(this).css('display', 'none');
    }
  });
};
$(window).scroll(scroll);
$(document.documentElement).scroll(scroll);

// Track resizing.
var resize = function () {
  $(cells).each(function () {
    // Get position.
    $(this).css({ 'position': 'relative', 'top': '0'});
    this.stickyPosition = $(this.cell).y();
    this.stickyMax = $(this.table).height();
  });
};
$(window).resize(resize);

// Track the element positions
$.fn.x = function(n) {
  var result = null;
  this.each(function() {
    var o = this;
    if (n === undefined) {
      var x = 0;
      if (o.offsetParent) {
	while (o.offsetParent) {
	  x += o.offsetLeft;
	  o = o.offsetParent;
	}
      }
      if (result === null) {
	result = x;
      } else {
	result = Math.min(result, x);
      }
    } else {
      o.style.left = n + 'px';
    }
  });
  return result;
};

$.fn.y = function(n) {
  var result = null;
  this.each(function() {
    var o = this;
    if (n === undefined) {
      var y = 0;
      if (o.offsetParent) {
	while (o.offsetParent) {
	  y += o.offsetTop;
	  o = o.offsetParent;
	}
      }
      if (result === null) {
	result = y;
      } else {
	result = Math.min(result, y);
      }
    } else {
      o.style.top = n + 'px';
    }
  });
  return result;
};

})(jQuery);;/*
 * select / unselect checkboxes plugin for jQuery
 *########################################
 *# Logic to select/unselect checkboxes  #
 *########################################
 * Last Action   Current Status	     Result
 *	         ( of the checkbox 
 *		   you selected )
 *
 * Checked       Checked             Uncheck the all checkboxes between the two
 * Checked       UnChecked	     Check the all checkboxes between the two
 * UnChecked     Checked	     Uncheck the all checkboxes between the two
 * UnChecked     UnChecked 	     Check the all checkboxes between the two
 */

(function($){

var lastChecked = null;

$(document).ready(function() {
    $('.form-checkbox').click(function(event) {
	//class selector is present?
	var isSelector = $(this).parent().parent().parent().parent().attr('class');
	//if not donot allow
	if (isSelector != 'selector' ) {
	    return;
	}
	if ( !lastChecked ) {
	    lastChecked = this;
	    return;
	}
	if ( event.shiftKey ) {
	    var start = $('.form-checkbox').index(this);
	    var end   = $('.form-checkbox').index(lastChecked);
	    if ( start == end ) {
		return;
	    }
	    var validLastcheck = $(lastChecked).parent().parent().attr('class');
	    var validthischeck = $(this).parent().parent().attr('class');
	    //don't allow the select/unslect other than search result e.g.(search form)
	    //TODO: once all headers are sticky( under development), we can remove the columnheader check	    
	    var params = new Array( "listing-box", "columnheader", "sticky", "" );
	    for ( i = 0; i < params.length; i++ ) {		
		if ( params[i] == validLastcheck || params[i] == validthischeck ) {
		    return;
		}
	    }
	    var min   = Math.min( start, end );
	    var max   = Math.max( start, end );
	    if ( lastChecked.checked && this.checked ) {
		lastChecked.checked = true;
	    } else if ( lastChecked.checked  && !this.checked ) {
	    	lastChecked.checked = false;
	    } else if ( !lastChecked.checked && this.checked  ) {
	    	lastChecked.checked = true;
	    } else if (! lastChecked.checked && !this.checked ) {
		lastChecked.checked = false;
	    } 
	    for ( i = min; i <= max; i++ ) {
		//check the checkboxes between the two chech boxes
		$('.form-checkbox')[i].checked = lastChecked.checked;
	    }
	    //add class for tr and remove if it unchecked
	    $('.selector tbody tr td:first-child input:checkbox').each( function() {
		var oldClass = $(this).parent().parent().attr('class');
		if ( this.checked ) {
		    $(this).parent().parent().removeClass().addClass('row-selected '+ oldClass);
		} else {
		    var lastClass = $(this).parent().parent().attr('class');
		    var str       = lastClass.toString().substring(12);
		    if ( lastClass.substring(0,12) == "row-selected" ) {
			$(this).parent().parent().removeClass().addClass(str);
		    }
		}	
	    });	    
	}
	lastChecked = this;
    });
});

})(jQuery);
;/* 
   jQuery TextAreaResizer plugin
*/
$(document).ready(function() {
    cj('textarea.huge:not(.processed)').TextAreaResizer();
    cj('textarea.form-textarea:not(.processed)').TextAreaResizer();
});

(function($) {
    /* private variable "oHover" used to determine if you're still hovering over the same element */
    var textarea, staticOffset; 
    // added the var declaration for 'staticOffset' thanks to issue logged by dec.
    var iLastMousePos = 0;
    //mininum text area height
    var iMin          = 32;
    
    var grip;
    /* TextAreaResizer plugin */
    $.fn.TextAreaResizer = function() {
	return this.each(function() {
	    textarea = $(this).addClass('processed'), staticOffset = null;
	    
	    // When wrapping the text area, work around an IE margin bug.  See:
	    // http://jaspan.com/ie-inherited-margin-bug-form-elements-and-haslayout
	    $(this).wrap('<div class="resizable-textarea"><span></span></div>')
	    .parent().append($('<div class="grippie"></div>').bind("mousedown",{el: this} , startDrag));

	    var grippie = $('div.grippie', $(this).parent())[0];
	    grippie.style.marginRight = (grippie.offsetWidth - $(this)[0].offsetWidth) +'px';
	});
    };
    
    /* private functions */
    function startDrag(e) {
	textarea = $(e.data.el);
	textarea.blur();
	iLastMousePos = mousePosition(e).y;
	staticOffset  = textarea.height() - iLastMousePos;
	textarea.css('opacity', 0.25);
	$(document).mousemove(performDrag).mouseup(endDrag);
	return false;
    }

    function performDrag(e) {
	var iThisMousePos = mousePosition(e).y;
	var iMousePos     = staticOffset + iThisMousePos;
	if ( iLastMousePos >= ( iThisMousePos ) ) {
	    iMousePos -= 5;
	}
	iLastMousePos = iThisMousePos;
	iMousePos     = Math.max(iMin, iMousePos);
	textarea.height( iMousePos + 'px' );
	if ( iMousePos < iMin ) {
	    endDrag(e);
	}
	return false;
    }
    
    function endDrag(e) {
	$(document).unbind('mousemove', performDrag).unbind('mouseup', endDrag);
	textarea.css('opacity', 1);
	textarea.focus();
	textarea      = null;
	staticOffset  = null;
	iLastMousePos = 0;
    }
    
    function mousePosition(e) {
	return { x: e.clientX + document.documentElement.scrollLeft, y: e.clientY + document.documentElement.scrollTop };
    };
})(jQuery);

;/*
 * jQuery Progress Bar plugin
 * Version 1.1.0 (06/20/2008)
 * @requires jQuery v1.2.1 or later
 *
 * Copyright (c) 2008 Gary Teo
 * http://t.wits.sg

USAGE:
	$(".someclass").progressBar();
	$("#progressbar").progressBar();
	$("#progressbar").progressBar(45);							// percentage
	$("#progressbar").progressBar({showText: false });			// percentage with config
	$("#progressbar").progressBar(45, {showText: false });		// percentage with config
*/
(function($) {
	$.extend({
		progressBar: new function() {

			this.defaults = {
				increment	: 2,
				speed		: 15,
				showText	: true,											// show text with percentage in next to the progressbar? - default : true
				width		: 120,											// Width of the progressbar - don't forget to adjust your image too!!!
				boxImage	: 'images/progressbar.gif',						// boxImage : image around the progress bar
				barImage	: {
								0:	'images/progressbg_red.gif',
								30: 'images/progressbg_orange.gif',
								70: 'images/progressbg_green.gif'
							},												// Image to use in the progressbar. Can be a single image too: 'images/progressbg_green.gif'
				height		: 12											// Height of the progressbar - don't forget to adjust your image too!!!
			};
			
			/* public methods */
			this.construct = function(arg1, arg2) {
				var argpercentage	= null;
				var argconfig		= null;
				
				if (arg1 != null) {
					if (!isNaN(arg1)) {
						argpercentage 	= arg1;
						if (arg2 != null) {
							argconfig	= arg2; }
					} else {
						argconfig		= arg1; 
					}
				}
				
				return this.each(function(child) {
					var pb		= this;
					if (argpercentage != null && this.bar != null && this.config != null) {
						this.config.tpercentage	= argpercentage;
						if (argconfig != null)
							pb.config			= $.extend(this.config, argconfig);
					} else {
						var $this				= $(this);
						var config				= $.extend({}, $.progressBar.defaults, argconfig);
						var percentage			= argpercentage;
						if (argpercentage == null)
							var percentage		= $this.html().replace("%","");	// parsed percentage
						
						
						$this.html("");
						var bar					= document.createElement('img');
						var text				= document.createElement('span');
						bar.id 					= this.id + "_percentImage";
						text.id 				= this.id + "_percentText";
						bar.title				= percentage + "%";
						bar.alt					= percentage + "%";
						bar.src					= config.boxImage;
						bar.width				= config.width;
						var $bar				= $(bar);
						var $text				= $(text);
						
						this.bar				= $bar;
						this.ntext				= $text;
						this.config				= config;
						this.config.cpercentage	= 0;
						this.config.tpercentage	= percentage;
						
						$bar.css("width", config.width + "px");
						$bar.css("height", config.height + "px");
						$bar.css("background-image", "url(" + getBarImage(this.config.cpercentage, config) + ")");
						$bar.css("padding", "0");
						$bar.css("margin", "0");
						$this.append($bar);
						$this.append($text);
					}
					
					function getBarImage (percentage, config) {
						var image = config.barImage;
						if (typeof(config.barImage) == 'object') {
							for (var i in config.barImage) {
								if (percentage >= parseInt(i)) {
									image = config.barImage[i];
								} else { break; }
							}
						}
						return image;
					}
					
					var t = setInterval(function() {
						var config		= pb.config;
						var cpercentage = parseInt(config.cpercentage);
						var tpercentage = parseInt(config.tpercentage);
						var increment	= parseInt(config.increment);
						var bar			= pb.bar;
						var text		= pb.ntext;
						var pixels		= config.width / 100;			// Define how many pixels go into 1%
						
						bar.css("background-image", "url(" + getBarImage(cpercentage, config) + ")");
						bar.css("background-position", (((config.width * -1)) + (cpercentage * pixels)) + 'px 50%');
						
						if (config.showText)
							text.html(" " + Math.round(cpercentage) + "%");
						
						if (cpercentage > tpercentage) {
							if (cpercentage - increment  < tpercentage) {
								pb.config.cpercentage = 0 + tpercentage
							} else {
								pb.config.cpercentage -= increment;
							}
						}
						else if (pb.config.cpercentage < pb.config.tpercentage) {
							if (cpercentage + increment  > tpercentage) {
								pb.config.cpercentage = tpercentage
							} else {
								pb.config.cpercentage += increment;
							}
						} 
						else {
							clearInterval(t);
						}
					}, pb.config.speed); 
				});
			};
		}
	});
		
	$.fn.extend({
        progressBar: $.progressBar.construct
	});
	
})(jQuery);;/*
 * jQuery Form Plugin
 * version: 2.25 (08-APR-2009)
 * @requires jQuery v1.2.2 or later
 *
 * Examples and documentation at: http://malsup.com/jquery/form/
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
;(function($) {

/*
    Usage Note:
    -----------
    Do not use both ajaxSubmit and ajaxForm on the same form.  These
    functions are intended to be exclusive.  Use ajaxSubmit if you want
    to bind your own submit handler to the form.  For example,

    $(document).ready(function() {
        $('#myForm').bind('submit', function() {
            $(this).ajaxSubmit({
                target: '#output'
            });
            return false; // <-- important!
        });
    });

    Use ajaxForm when you want the plugin to manage all the event binding
    for you.  For example,

    $(document).ready(function() {
        $('#myForm').ajaxForm({
            target: '#output'
        });
    });

    When using ajaxForm, the ajaxSubmit function will be invoked for you
    at the appropriate time.
*/

/**
 * ajaxSubmit() provides a mechanism for immediately submitting
 * an HTML form using AJAX.
 */
$.fn.ajaxSubmit = function(options) {
    // fast fail if nothing selected (http://dev.jquery.com/ticket/2752)
    if (!this.length) {
        log('ajaxSubmit: skipping submit process - no element selected');
        return this;
    }

    if (typeof options == 'function')
        options = { success: options };

    // clean url (don't include hash vaue)
    var url = this.attr('action') || window.location.href;
    url = (url.match(/^([^#]+)/)||[])[1];
    url = url || '';

    options = $.extend({
        url:  url,
        type: this.attr('method') || 'GET'
    }, options || {});

    // hook for manipulating the form data before it is extracted;
    // convenient for use with rich editors like tinyMCE or FCKEditor
    var veto = {};
    this.trigger('form-pre-serialize', [this, options, veto]);
    if (veto.veto) {
        log('ajaxSubmit: submit vetoed via form-pre-serialize trigger');
        return this;
    }

    // provide opportunity to alter form data before it is serialized
    if (options.beforeSerialize && options.beforeSerialize(this, options) === false) {
        log('ajaxSubmit: submit aborted via beforeSerialize callback');
        return this;
    }

    var a = this.formToArray(options.semantic);
    if (options.data) {
        options.extraData = options.data;
        for (var n in options.data) {
          if(options.data[n] instanceof Array) {
            for (var k in options.data[n])
              a.push( { name: n, value: options.data[n][k] } );
          }
          else
             a.push( { name: n, value: options.data[n] } );
        }
    }

    // give pre-submit callback an opportunity to abort the submit
    if (options.beforeSubmit && options.beforeSubmit(a, this, options) === false) {
        log('ajaxSubmit: submit aborted via beforeSubmit callback');
        return this;
    }

    // fire vetoable 'validate' event
    this.trigger('form-submit-validate', [a, this, options, veto]);
    if (veto.veto) {
        log('ajaxSubmit: submit vetoed via form-submit-validate trigger');
        return this;
    }

    var q = $.param(a);

    if (options.type.toUpperCase() == 'GET') {
        options.url += (options.url.indexOf('?') >= 0 ? '&' : '?') + q;
        options.data = null;  // data is null for 'get'
    }
    else
        options.data = q; // data is the query string for 'post'

    var $form = this, callbacks = [];
    if (options.resetForm) callbacks.push(function() { $form.resetForm(); });
    if (options.clearForm) callbacks.push(function() { $form.clearForm(); });

    // perform a load on the target only if dataType is not provided
    if (!options.dataType && options.target) {
        var oldSuccess = options.success || function(){};
        callbacks.push(function(data) {
            $(options.target).html(data).each(oldSuccess, arguments);
        });
    }
    else if (options.success)
        callbacks.push(options.success);

    options.success = function(data, status) {
        for (var i=0, max=callbacks.length; i < max; i++)
            callbacks[i].apply(options, [data, status, $form]);
    };

    // are there files to upload?
    var files = $('input:file', this).fieldValue();
    var found = false;
    for (var j=0; j < files.length; j++)
        if (files[j])
            found = true;

    // options.iframe allows user to force iframe mode
   if (options.iframe || found) {
       // hack to fix Safari hang (thanks to Tim Molendijk for this)
       // see:  http://groups.google.com/group/jquery-dev/browse_thread/thread/36395b7ab510dd5d
       if (options.closeKeepAlive)
           $.get(options.closeKeepAlive, fileUpload);
       else
           fileUpload();
       }
   else
       $.ajax(options);

    // fire 'notify' event
    this.trigger('form-submit-notify', [this, options]);
    return this;


    // private function for handling file uploads (hat tip to YAHOO!)
    function fileUpload() {
        var form = $form[0];

        if ($(':input[name=submit]', form).length) {
            alert('Error: Form elements must not be named "submit".');
            return;
        }

        var opts = $.extend({}, $.ajaxSettings, options);
		var s = $.extend(true, {}, $.extend(true, {}, $.ajaxSettings), opts);

        var id = 'jqFormIO' + (new Date().getTime());
        var $io = $('<iframe id="' + id + '" name="' + id + '" src="about:blank" />');
        var io = $io[0];

        $io.css({ position: 'absolute', top: '-1000px', left: '-1000px' });

        var xhr = { // mock object
            aborted: 0,
            responseText: null,
            responseXML: null,
            status: 0,
            statusText: 'n/a',
            getAllResponseHeaders: function() {},
            getResponseHeader: function() {},
            setRequestHeader: function() {},
            abort: function() {
                this.aborted = 1;
                $io.attr('src','about:blank'); // abort op in progress
            }
        };

        var g = opts.global;
        // trigger ajax global events so that activity/block indicators work like normal
        if (g && ! $.active++) $.event.trigger("ajaxStart");
        if (g) $.event.trigger("ajaxSend", [xhr, opts]);

		if (s.beforeSend && s.beforeSend(xhr, s) === false) {
			s.global && $.active--;
			return;
        }
        if (xhr.aborted)
            return;

        var cbInvoked = 0;
        var timedOut = 0;

        // add submitting element to data if we know it
        var sub = form.clk;
        if (sub) {
            var n = sub.name;
            if (n && !sub.disabled) {
                options.extraData = options.extraData || {};
                options.extraData[n] = sub.value;
                if (sub.type == "image") {
                    options.extraData[name+'.x'] = form.clk_x;
                    options.extraData[name+'.y'] = form.clk_y;
                }
            }
        }

        // take a breath so that pending repaints get some cpu time before the upload starts
        setTimeout(function() {
            // make sure form attrs are set
            var t = $form.attr('target'), a = $form.attr('action');

			// update form attrs in IE friendly way
			form.setAttribute('target',id);
			if (form.getAttribute('method') != 'POST')
				form.setAttribute('method', 'POST');
			if (form.getAttribute('action') != opts.url)
				form.setAttribute('action', opts.url);

            // ie borks in some cases when setting encoding
            if (! options.skipEncodingOverride) {
                $form.attr({
                    encoding: 'multipart/form-data',
                    enctype:  'multipart/form-data'
                });
            }

            // support timout
            if (opts.timeout)
                setTimeout(function() { timedOut = true; cb(); }, opts.timeout);

            // add "extra" data to form if provided in options
            var extraInputs = [];
            try {
                if (options.extraData)
                    for (var n in options.extraData)
                        extraInputs.push(
                            $('<input type="hidden" name="'+n+'" value="'+options.extraData[n]+'" />')
                                .appendTo(form)[0]);

                // add iframe to doc and submit the form
                $io.appendTo('body');
                io.attachEvent ? io.attachEvent('onload', cb) : io.addEventListener('load', cb, false);
                form.submit();
            }
            finally {
                // reset attrs and remove "extra" input elements
				form.setAttribute('action',a);
                t ? form.setAttribute('target', t) : $form.removeAttr('target');
                $(extraInputs).remove();
            }
        }, 10);

        var nullCheckFlag = 0;

        function cb() {
            if (cbInvoked++) return;

            io.detachEvent ? io.detachEvent('onload', cb) : io.removeEventListener('load', cb, false);

            var ok = true;
            try {
                if (timedOut) throw 'timeout';
                // extract the server response from the iframe
                var data, doc;

                doc = io.contentWindow ? io.contentWindow.document : io.contentDocument ? io.contentDocument : io.document;

                if ((doc.body == null || doc.body.innerHTML == '') && !nullCheckFlag) {
                    // in some browsers (cough, Opera 9.2.x) the iframe DOM is not always traversable when
                    // the onload callback fires, so we give them a 2nd chance
                    nullCheckFlag = 1;
                    cbInvoked--;
                    setTimeout(cb, 100);
                    return;
                }

                xhr.responseText = doc.body ? doc.body.innerHTML : null;
                xhr.responseXML = doc.XMLDocument ? doc.XMLDocument : doc;
                xhr.getResponseHeader = function(header){
                    var headers = {'content-type': opts.dataType};
                    return headers[header];
                };

                if (opts.dataType == 'json' || opts.dataType == 'script') {
                    var ta = doc.getElementsByTagName('textarea')[0];
                    xhr.responseText = ta ? ta.value : xhr.responseText;
                }
                else if (opts.dataType == 'xml' && !xhr.responseXML && xhr.responseText != null) {
                    xhr.responseXML = toXml(xhr.responseText);
                }
                data = $.httpData(xhr, opts.dataType);
            }
            catch(e){
                ok = false;
                $.handleError(opts, xhr, 'error', e);
            }

            // ordering of these callbacks/triggers is odd, but that's how $.ajax does it
            if (ok) {
                opts.success(data, 'success');
                if (g) $.event.trigger("ajaxSuccess", [xhr, opts]);
            }
            if (g) $.event.trigger("ajaxComplete", [xhr, opts]);
            if (g && ! --$.active) $.event.trigger("ajaxStop");
            if (opts.complete) opts.complete(xhr, ok ? 'success' : 'error');

            // clean up
            setTimeout(function() {
                $io.remove();
                xhr.responseXML = null;
            }, 100);
        };

        function toXml(s, doc) {
            if (window.ActiveXObject) {
                doc = new ActiveXObject('Microsoft.XMLDOM');
                doc.async = 'false';
                doc.loadXML(s);
            }
            else
                doc = (new DOMParser()).parseFromString(s, 'text/xml');
            return (doc && doc.documentElement && doc.documentElement.tagName != 'parsererror') ? doc : null;
        };
    };
};

/**
 * ajaxForm() provides a mechanism for fully automating form submission.
 *
 * The advantages of using this method instead of ajaxSubmit() are:
 *
 * 1: This method will include coordinates for <input type="image" /> elements (if the element
 *    is used to submit the form).
 * 2. This method will include the submit element's name/value data (for the element that was
 *    used to submit the form).
 * 3. This method binds the submit() method to the form for you.
 *
 * The options argument for ajaxForm works exactly as it does for ajaxSubmit.  ajaxForm merely
 * passes the options argument along after properly binding events for submit elements and
 * the form itself.
 */
$.fn.ajaxForm = function(options) {
    return this.ajaxFormUnbind().bind('submit.form-plugin',function() {
        $(this).ajaxSubmit(options);
        return false;
    }).each(function() {
        // store options in hash
        $(":submit,input:image", this).bind('click.form-plugin',function(e) {
            var form = this.form;
            form.clk = this;
            if (this.type == 'image') {
                if (e.offsetX != undefined) {
                    form.clk_x = e.offsetX;
                    form.clk_y = e.offsetY;
                } else if (typeof $.fn.offset == 'function') { // try to use dimensions plugin
                    var offset = $(this).offset();
                    form.clk_x = e.pageX - offset.left;
                    form.clk_y = e.pageY - offset.top;
                } else {
                    form.clk_x = e.pageX - this.offsetLeft;
                    form.clk_y = e.pageY - this.offsetTop;
                }
            }
            // clear form vars
            setTimeout(function() { form.clk = form.clk_x = form.clk_y = null; }, 10);
        });
    });
};

// ajaxFormUnbind unbinds the event handlers that were bound by ajaxForm
$.fn.ajaxFormUnbind = function() {
    this.unbind('submit.form-plugin');
    return this.each(function() {
        $(":submit,input:image", this).unbind('click.form-plugin');
    });

};

/**
 * formToArray() gathers form element data into an array of objects that can
 * be passed to any of the following ajax functions: $.get, $.post, or load.
 * Each object in the array has both a 'name' and 'value' property.  An example of
 * an array for a simple login form might be:
 *
 * [ { name: 'username', value: 'jresig' }, { name: 'password', value: 'secret' } ]
 *
 * It is this array that is passed to pre-submit callback functions provided to the
 * ajaxSubmit() and ajaxForm() methods.
 */
$.fn.formToArray = function(semantic) {
    var a = [];
    if (this.length == 0) return a;

    var form = this[0];
    var els = semantic ? form.getElementsByTagName('*') : form.elements;
    if (!els) return a;
    for(var i=0, max=els.length; i < max; i++) {
        var el = els[i];
        var n = el.name;
        if (!n) continue;

        if (semantic && form.clk && el.type == "image") {
            // handle image inputs on the fly when semantic == true
            if(!el.disabled && form.clk == el)
                a.push({name: n+'.x', value: form.clk_x}, {name: n+'.y', value: form.clk_y});
            continue;
        }

        var v = $.fieldValue(el, true);
        if (v && v.constructor == Array) {
            for(var j=0, jmax=v.length; j < jmax; j++)
                a.push({name: n, value: v[j]});
        }
        else if (v !== null && typeof v != 'undefined')
            a.push({name: n, value: v});
    }

    if (!semantic && form.clk) {
        // input type=='image' are not found in elements array! handle them here
        var inputs = form.getElementsByTagName("input");
        for(var i=0, max=inputs.length; i < max; i++) {
            var input = inputs[i];
            var n = input.name;
            if(n && !input.disabled && input.type == "image" && form.clk == input)
                a.push({name: n+'.x', value: form.clk_x}, {name: n+'.y', value: form.clk_y});
        }
    }
    return a;
};

/**
 * Serializes form data into a 'submittable' string. This method will return a string
 * in the format: name1=value1&amp;name2=value2
 */
$.fn.formSerialize = function(semantic) {
    //hand off to jQuery.param for proper encoding
    return $.param(this.formToArray(semantic));
};

/**
 * Serializes all field elements in the jQuery object into a query string.
 * This method will return a string in the format: name1=value1&amp;name2=value2
 */
$.fn.fieldSerialize = function(successful) {
    var a = [];
    this.each(function() {
        var n = this.name;
        if (!n) return;
        var v = $.fieldValue(this, successful);
        if (v && v.constructor == Array) {
            for (var i=0,max=v.length; i < max; i++)
                a.push({name: n, value: v[i]});
        }
        else if (v !== null && typeof v != 'undefined')
            a.push({name: this.name, value: v});
    });
    //hand off to jQuery.param for proper encoding
    return $.param(a);
};

/**
 * Returns the value(s) of the element in the matched set.  For example, consider the following form:
 *
 *  <form><fieldset>
 *      <input name="A" type="text" />
 *      <input name="A" type="text" />
 *      <input name="B" type="checkbox" value="B1" />
 *      <input name="B" type="checkbox" value="B2"/>
 *      <input name="C" type="radio" value="C1" />
 *      <input name="C" type="radio" value="C2" />
 *  </fieldset></form>
 *
 *  var v = $(':text').fieldValue();
 *  // if no values are entered into the text inputs
 *  v == ['','']
 *  // if values entered into the text inputs are 'foo' and 'bar'
 *  v == ['foo','bar']
 *
 *  var v = $(':checkbox').fieldValue();
 *  // if neither checkbox is checked
 *  v === undefined
 *  // if both checkboxes are checked
 *  v == ['B1', 'B2']
 *
 *  var v = $(':radio').fieldValue();
 *  // if neither radio is checked
 *  v === undefined
 *  // if first radio is checked
 *  v == ['C1']
 *
 * The successful argument controls whether or not the field element must be 'successful'
 * (per http://www.w3.org/TR/html4/interact/forms.html#successful-controls).
 * The default value of the successful argument is true.  If this value is false the value(s)
 * for each element is returned.
 *
 * Note: This method *always* returns an array.  If no valid value can be determined the
 *       array will be empty, otherwise it will contain one or more values.
 */
$.fn.fieldValue = function(successful) {
    for (var val=[], i=0, max=this.length; i < max; i++) {
        var el = this[i];
        var v = $.fieldValue(el, successful);
        if (v === null || typeof v == 'undefined' || (v.constructor == Array && !v.length))
            continue;
        v.constructor == Array ? $.merge(val, v) : val.push(v);
    }
    return val;
};

/**
 * Returns the value of the field element.
 */
$.fieldValue = function(el, successful) {
    var n = el.name, t = el.type, tag = el.tagName.toLowerCase();
    if (typeof successful == 'undefined') successful = true;

    if (successful && (!n || el.disabled || t == 'reset' || t == 'button' ||
        (t == 'checkbox' || t == 'radio') && !el.checked ||
        (t == 'submit' || t == 'image') && el.form && el.form.clk != el ||
        tag == 'select' && el.selectedIndex == -1))
            return null;

    if (tag == 'select') {
        var index = el.selectedIndex;
        if (index < 0) return null;
        var a = [], ops = el.options;
        var one = (t == 'select-one');
        var max = (one ? index+1 : ops.length);
        for(var i=(one ? index : 0); i < max; i++) {
            var op = ops[i];
            if (op.selected) {
				var v = op.value;
				if (!v) // extra pain for IE...
                	v = (op.attributes && op.attributes['value'] && !(op.attributes['value'].specified)) ? op.text : op.value;
                if (one) return v;
                a.push(v);
            }
        }
        return a;
    }
    return el.value;
};

/**
 * Clears the form data.  Takes the following actions on the form's input fields:
 *  - input text fields will have their 'value' property set to the empty string
 *  - select elements will have their 'selectedIndex' property set to -1
 *  - checkbox and radio inputs will have their 'checked' property set to false
 *  - inputs of type submit, button, reset, and hidden will *not* be effected
 *  - button elements will *not* be effected
 */
$.fn.clearForm = function() {
    return this.each(function() {
        $('input,select,textarea', this).clearFields();
    });
};

/**
 * Clears the selected form elements.
 */
$.fn.clearFields = $.fn.clearInputs = function() {
    return this.each(function() {
        var t = this.type, tag = this.tagName.toLowerCase();
        if (t == 'text' || t == 'password' || tag == 'textarea')
            this.value = '';
        else if (t == 'checkbox' || t == 'radio')
            this.checked = false;
        else if (tag == 'select')
            this.selectedIndex = -1;
    });
};

/**
 * Resets the form data.  Causes all form elements to be reset to their original value.
 */
$.fn.resetForm = function() {
    return this.each(function() {
        // guard against an input with the name of 'reset'
        // note that IE reports the reset function as an 'object'
        if (typeof this.reset == 'function' || (typeof this.reset == 'object' && !this.reset.nodeType))
            this.reset();
    });
};

/**
 * Enables or disables any matching elements.
 */
$.fn.enable = function(b) {
    if (b == undefined) b = true;
    return this.each(function() {
        this.disabled = !b;
    });
};

/**
 * Checks/unchecks any matching checkboxes or radio buttons and
 * selects/deselects and matching option elements.
 */
$.fn.selected = function(select) {
    if (select == undefined) select = true;
    return this.each(function() {
        var t = this.type;
        if (t == 'checkbox' || t == 'radio')
            this.checked = select;
        else if (this.tagName.toLowerCase() == 'option') {
            var $sel = $(this).parent('select');
            if (select && $sel[0] && $sel[0].type == 'select-one') {
                // deselect all other options
                $sel.find('option').selected(false);
            }
            this.selected = select;
        }
    });
};

// helper fn for console logging
// set $.fn.ajaxSubmit.debug to true to enable debug logging
function log() {
    if ($.fn.ajaxSubmit.debug && window.console && window.console.log)
        window.console.log('[jquery.form] ' + Array.prototype.join.call(arguments,''));
};

})(jQuery);
;/*
 * jQuery Plugin: Tokenizing Autocomplete Text Entry
 * Version 1.1
 *
 * Copyright (c) 2009 James Smith (http://loopj.com)
 * Licensed jointly under the GPL and MIT licenses,
 * choose which one suits your project best!
 *
 */

(function($) {

$.fn.tokenInput = function (url, options) {
    var settings = $.extend({
        url: url,
        hintText: options.hintText,
        noResultsText: "No results",
        searchingText: "Searching...",
        searchDelay: 600,
        minChars: 2,
        tokenLimit: null,
        jsonContainer: null,
        method: "GET",
        contentType: "json",
        queryParam: "name",
        ajaxCallbackFunction: null,
        onResult: null
    }, options);

    settings.classes = $.extend({
        tokenList: "token-input-list",
        token: "token-input-token",
        tokenDelete: "token-input-delete-token",
        selectedToken: "token-input-selected-token",
        highlightedToken: "token-input-highlighted-token",
        dropdown: "token-input-dropdown",
        dropdownItem: "token-input-dropdown-item",
        dropdownItem2: "token-input-dropdown-item2",
        selectedDropdownItem: "token-input-selected-dropdown-item",
        inputToken: "token-input-input-token"
    }, options.classes);

    return this.each(function () {
        var list = new $.TokenList(this, settings);
    });
};

$.TokenList = function (input, settings) {
    //
    // Variables
    //

    // Input box position "enum"
    var POSITION = {
        BEFORE: 0,
        AFTER: 1,
        END: 2
    };

    // Keys "enum"
    var KEY = {
        BACKSPACE: 8,
        TAB: 9,
        RETURN: 13,
        ESC: 27,
        LEFT: 37,
        UP: 38,
        RIGHT: 39,
        DOWN: 40,
        COMMA: 188
    };

    // Save the tokens
    var saved_tokens = [];
    
    // Keep track of the number of tokens in the list
    var token_count = 0;

    // Basic cache to save on db hits
    var cache = new $.TokenList.Cache();

    // Keep track of the timeout
    var timeout;

    // Create a new text input an attach keyup events
    var input_box = $("<input type=\"text\">")
        .css({
            outline: "none"
        })
        .focus(function () {
            if (settings.tokenLimit == null || settings.tokenLimit != token_count) {
                show_dropdown_hint();
            }
        })
        .blur(function () {
            hide_dropdown();
        })
        .keydown(function (event) {
            var previous_token;
            var next_token;

            switch(event.keyCode) {
                case KEY.LEFT:
                case KEY.RIGHT:
                case KEY.UP:
                case KEY.DOWN:
                    if(!$(this).val()) {
                        previous_token = input_token.prev();
                        next_token = input_token.next();

                        if((previous_token.length && previous_token.get(0) === selected_token) || (next_token.length && next_token.get(0) === selected_token)) {
                            // Check if there is a previous/next token and it is selected
                            if(event.keyCode == KEY.LEFT || event.keyCode == KEY.UP) {
                                deselect_token($(selected_token), POSITION.BEFORE);
                            } else {
                                deselect_token($(selected_token), POSITION.AFTER);
                            }
                        } else if((event.keyCode == KEY.LEFT || event.keyCode == KEY.UP) && previous_token.length) {
                            // We are moving left, select the previous token if it exists
                            select_token($(previous_token.get(0)));
                        } else if((event.keyCode == KEY.RIGHT || event.keyCode == KEY.DOWN) && next_token.length) {
                            // We are moving right, select the next token if it exists
                            select_token($(next_token.get(0)));
                        }
                    } else {
                        var dropdown_item = null;

                        if(event.keyCode == KEY.DOWN || event.keyCode == KEY.RIGHT) {
                            dropdown_item = $(selected_dropdown_item).next();
                        } else {
                            dropdown_item = $(selected_dropdown_item).prev();
                        }

                        if(dropdown_item.length) {
                            select_dropdown_item(dropdown_item);
                        }
                        return false;
                    }
                    break;

                case KEY.BACKSPACE:
                    previous_token = input_token.prev();

                    if(!$(this).val().length) {
                        if(selected_token) {
                            delete_token($(selected_token));
                        } else if(previous_token.length) {
                            select_token($(previous_token.get(0)));
                        }

                        return false;
                    } else if($(this).val().length == 1) {
                        hide_dropdown();
                    } else {
                        // set a timeout just long enough to let this function finish.
                        setTimeout(function(){do_search(false);}, 5);
                    }
                    break;

                case KEY.TAB:
                case KEY.RETURN:
                case KEY.COMMA:
                  if(selected_dropdown_item) {
                    add_token($(selected_dropdown_item));
                    return false;
                  }
                  break;

                case KEY.ESC:
                  hide_dropdown();
                  return true;

                default:
                    if(is_printable_character(event.keyCode)) {
                      // set a timeout just long enough to let this function finish.
                      setTimeout(function(){do_search(false);}, 5);
                    }
                    break;
            }
        });

    // Keep a reference to the original input box
    var hidden_input = $(input)
                           .hide()
                           .focus(function () {
                               input_box.focus();
                           })
                           .blur(function () {
                               input_box.blur();
                           });

    // Keep a reference to the selected token and dropdown item
    var selected_token = null;
    var selected_dropdown_item = null;

    // The list to store the token items in
    var token_list = $("<ul />")
        .addClass(settings.classes.tokenList)
        .insertAfter(hidden_input)
        .click(function (event) {
            var li = get_element_from_event(event, "li");
            if(li && li.get(0) != input_token.get(0)) {
                toggle_select_token(li);
                return false;
            } else {
                input_box.focus();

                if(selected_token) {
                    deselect_token($(selected_token), POSITION.END);
                }
            }
        })
        .mouseover(function (event) {
            var li = get_element_from_event(event, "li");
            if(li && selected_token !== this) {
                li.addClass(settings.classes.highlightedToken);
            }
        })
        .mouseout(function (event) {
            var li = get_element_from_event(event, "li");
            if(li && selected_token !== this) {
                li.removeClass(settings.classes.highlightedToken);
            }
        })
        .mousedown(function (event) {
            // Stop user selecting text on tokens
            var li = get_element_from_event(event, "li");
            if(li){
                return false;
            }
        });


    // The list to store the dropdown items in
    var dropdown = $("<div>")
        .addClass(settings.classes.dropdown)
        .insertAfter(token_list)
        .hide();

    // The token holding the input box
    var input_token = $("<li />")
        .addClass(settings.classes.inputToken)
        .appendTo(token_list)
        .append(input_box);

    init_list();

    //
    // Functions
    //


    // Pre-populate list if items exist
    function init_list () {
        //reset tokens, before initialize.
        hidden_input.val('');
       
        li_data = settings.prePopulate;
        if(li_data && li_data.length) {
          for(var i in li_data) { 
              if ( li_data[i].id ) {
                    var this_token = $("<li><p>"+li_data[i].name+"</p> </li>")
                        .addClass(settings.classes.token)
                        .insertBefore(input_token);

                    $("<span>x</span>")
                        .addClass(settings.classes.tokenDelete)
                        .appendTo(this_token)
                        .click(function () {
                            delete_token($(this).parent());
                            return false;
                        });

                    $.data(this_token.get(0), "tokeninput", {"id": li_data[i].id, "name": li_data[i].name});

                    // Clear input box and make sure it keeps focus
                    input_box
                        .val("")
                        .focus();

                    // Don't show the help dropdown, they've got the idea
                    hide_dropdown();

                    // Save this token id
                    var id_string = li_data[i].id;
                    
                    // IE fixes: Remove extra comma at the end
                    if ( hidden_input.val( ) ) {
                        id_string = ',' + id_string;    
                    }
                    
                    hidden_input.val(hidden_input.val() + id_string);
                }
            }
        }
    }

    function is_printable_character(keycode) {
        if((keycode >= 48 && keycode <= 90) ||      // 0-1a-z
           (keycode >= 96 && keycode <= 111) ||     // numpad 0-9 + - / * .
           (keycode >= 186 && keycode <= 192) ||    // ; = , - . / ^
           (keycode >= 219 && keycode <= 222)       // ( \ ) '
          ) {
              return true;
          } else {
              return false;
          }
    }

    // Get an element of a particular type from an event (click/mouseover etc)
    function get_element_from_event (event, element_type) {
        var target = $(event.target);
        var element = null;

        if(target.is(element_type)) {
            element = target;
        } else if(target.parent(element_type).length) {
            element = target.parent(element_type+":first");
        }

        return element;
    }

    // Inner function to a token to the list
    function insert_token(id, value) {
      var this_token = $("<li><p>"+ value +"</p> </li>")
      .addClass(settings.classes.token)
      .insertBefore(input_token);

      // The 'delete token' button
      $("<span>x</span>")
          .addClass(settings.classes.tokenDelete)
          .appendTo(this_token)
          .click(function () {
              delete_token($(this).parent());
              return false;
          });

      $.data(this_token.get(0), "tokeninput", {"id": id, "name": value});

      return this_token;
    }

    // Add a token to the token list based on user input
    function add_token (item) {
        var li_data = $.data(item.get(0), "tokeninput");
        var this_token = insert_token(li_data.id, li_data.name);

        // Clear input box and make sure it keeps focus
        input_box
            .val("")
            .focus();

        // Don't show the help dropdown, they've got the idea
        hide_dropdown();

        // Save this token id
        var id_string = li_data.id;
        
        if ( settings.ajaxCallbackFunction !=  null ) {
            eval( settings.ajaxCallbackFunction + '( "select", id_string )')
        }

        // IE fixes: Remove extra comma at the end
        if ( hidden_input.val( ) ) {
		    id_string = ',' + id_string;    
		}
		
        hidden_input.val(hidden_input.val() + id_string);
        
        token_count++;
        
        if(settings.tokenLimit != null && settings.tokenLimit >= token_count) {
            input_box.hide();
            hide_dropdown();
        }
    }

    // Select a token in the token list
    function select_token (token) {
        token.addClass(settings.classes.selectedToken);
        selected_token = token.get(0);

        // Hide input box
        input_box.val("");

        // Hide dropdown if it is visible (eg if we clicked to select token)
        hide_dropdown();
    }

    // Deselect a token in the token list
    function deselect_token (token, position) {
        token.removeClass(settings.classes.selectedToken);
        selected_token = null;

        if(position == POSITION.BEFORE) {
            input_token.insertBefore(token);
        } else if(position == POSITION.AFTER) {
            input_token.insertAfter(token);
        } else {
            input_token.appendTo(token_list);
        }

        // Show the input box and give it focus again
        input_box.focus();
    }

    // Toggle selection of a token in the token list
    function toggle_select_token (token) {
        if(selected_token == token.get(0)) {
            deselect_token(token, POSITION.END);
        } else {
            if(selected_token) {
                deselect_token($(selected_token), POSITION.END);
            }
            select_token(token);
        }
    }

    // Delete a token from the token list
    function delete_token (token) {
        // Remove the id from the saved list
        var token_data = $.data(token.get(0), "tokeninput");
        
        if ( settings.ajaxCallbackFunction !=  null ) {
            eval( settings.ajaxCallbackFunction + '( "delete", token_data.id )')
        }

        // Delete the token
        token.remove();
        selected_token = null;

        // Show the input box and give it focus again
        input_box.focus();

        // Delete this token's id from hidden input
        var str = hidden_input.val()

        // var start = str.indexOf(token_data.id+",");
        // var end = str.indexOf(",", start) + 1;
        // 
        // if(end >= str.length) {
        //     hidden_input.val(str.slice(0, start));
        // } else {
        //     hidden_input.val(str.slice(0, start) + str.slice(end, str.length));
        // }
        
        // IE : Fixes to remove extra comma
        var resultantTokenStr = '';
    	// token string consist of multiple tokens.
    	if ( str.indexOf( ',' ) != -1 ) {
    	    var deleteTokenStr = ',' + token_data.id;
    	    if ( str.indexOf( token_data.id ) == 0 ) {
    		    deleteTokenStr =  token_data.id + ',';
    	    }
    	    var start = str.indexOf( deleteTokenStr );
    	    var end   = start + deleteTokenStr.length;
    	    resultantTokenStr = str.slice( 0, start ) + str.slice( end, str.length );
    	}

    	hidden_input.val( resultantTokenStr );
    	
        token_count--;
        
        if (settings.tokenLimit != null) {
            input_box
                .show()
                .val("")
                .focus();
        }
    }

    // Hide and clear the results dropdown
    function hide_dropdown () {
//         dropdown.hide().empty();
//         selected_dropdown_item = null;

      //here we need to sleep, fix for IE, CRM-6012 
      setTimeout(function( ) { 
	  dropdown.hide().empty(); 
	  selected_dropdown_item = null; 
	}, 300 );
    }

    function show_dropdown_searching () {
        dropdown
            .html("<p>"+settings.searchingText+"</p>")
            .show();
    }

    function show_dropdown_hint () {
        dropdown
            .html("<p>"+settings.hintText+"</p>")
            .show();
    }

    // Highlight the query part of the search term
	function highlight_term(value, term) {
		return value.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + term + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<b>$1</b>");
	}

    // Populate the results dropdown with some results
    function populate_dropdown (query, results) {
        if(results.length) {
            dropdown.empty();
            var dropdown_ul = $("<ul>")
                .appendTo(dropdown)
                .mouseover(function (event) {
                    select_dropdown_item(get_element_from_event(event, "li"));
                })
                .click(function (event) {
                    add_token(get_element_from_event(event, "li"));
                })
                .mousedown(function (event) {
                    // Stop user selecting text on tokens
                    return false;
                })
                .hide();

            for(var i in results) {
                if (results.hasOwnProperty(i)) {
                    var this_li = $("<li>"+highlight_term(results[i].name, query)+"</li>")
                                      .appendTo(dropdown_ul);

                    if(i%2) {
                        this_li.addClass(settings.classes.dropdownItem);
                    } else {
                        this_li.addClass(settings.classes.dropdownItem2);
                    }

                    if(i == 0) {
                        select_dropdown_item(this_li);
                    }

                    $.data(this_li.get(0), "tokeninput", {"id": results[i].id, "name": results[i].name});
                }
            }

            dropdown.show();
            dropdown_ul.slideDown("fast");

        } else {
            dropdown
                .html("<p>"+settings.noResultsText+"</p>")
                .show();
        }
    }

    // Highlight an item in the results dropdown
    function select_dropdown_item (item) {
        if(item) {
            if(selected_dropdown_item) {
                deselect_dropdown_item($(selected_dropdown_item));
            }

            item.addClass(settings.classes.selectedDropdownItem);
            selected_dropdown_item = item.get(0);
        }
    }

    // Remove highlighting from an item in the results dropdown
    function deselect_dropdown_item (item) {
        item.removeClass(settings.classes.selectedDropdownItem);
        selected_dropdown_item = null;
    }

    // Do a search and show the "searching" dropdown if the input is longer
    // than settings.minChars
    function do_search(immediate) {
        var query = input_box.val().toLowerCase();

        if (query && query.length) {
            if(selected_token) {
                deselect_token($(selected_token), POSITION.AFTER);
            }
            if (query.length >= settings.minChars) {
                show_dropdown_searching();
                if (immediate) {
                    run_search(query);
                } else {
                    clearTimeout(timeout);
                    timeout = setTimeout(function(){run_search(query);}, settings.searchDelay);
                }
            } else {
                hide_dropdown();
            }
        }
    }

    // Do the actual search
    function run_search(query) {
        var cached_results = cache.get(query);
        if(cached_results) {
            populate_dropdown(query, cached_results);
        } else {
			var queryStringDelimiter = settings.url.indexOf("?") < 0 ? "?" : "&";
			var callback = function(results) {
			  if($.isFunction(settings.onResult)) {
			      results = settings.onResult.call(this, results);
			  }
              cache.add(query, settings.jsonContainer ? results[settings.jsonContainer] : results);
              populate_dropdown(query, settings.jsonContainer ? results[settings.jsonContainer] : results);
            };
            
            if(settings.method == "POST") {
			    $.post(settings.url + queryStringDelimiter + settings.queryParam + "=" + query, {}, callback, settings.contentType);
		    } else {
		        $.get(settings.url + queryStringDelimiter + settings.queryParam + "=" + query, {}, callback, settings.contentType);
		    }
        }
    }
};

// Really basic cache for the results
$.TokenList.Cache = function (options) {
    var settings = $.extend({
        max_size: 50
    }, options);

    var data = {};
    var size = 0;

    var flush = function () {
        data = {};
        size = 0;
    };

    this.add = function (query, results) {
        if(size > settings.max_size) {
            flush();
        }

        if(!data[query]) {
            size++;
        }

        data[query] = results;
    };

    this.get = function (query) {
        return data[query];
    };
};

})(jQuery);
;/* http://keith-wood.name/timeEntry.html
   Time entry for jQuery v1.4.6.
   Written by Keith Wood (kbwood{at}iinet.com.au) June 2007.
   Dual licensed under the GPL (http://dev.jquery.com/browser/trunk/jquery/GPL-LICENSE.txt) and 
   MIT (http://dev.jquery.com/browser/trunk/jquery/MIT-LICENSE.txt) licenses. 
   Please attribute the author if you use it. */
eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(q($){q 1j(){o.X=[];o.22=[];o.22[\'\']={1b:A,1c:\':\',1k:\'\',Y:[\'3H\',\'3I\'],2A:[\'3J\',\'3K 2B\',\'3L 2B\',\'3M\',\'3N\']};o.1z={2C:\'\',1A:A,25:[1,1,1],2D:0,2E:K,2F:u,2G:u,2H:u,2I:\'3O.3P\',1l:[20,20,8],2J:\'\',1B:[40,40,16],2K:A,26:[3Q,3R],2L:u,2M:u};$.1m(o.1z,o.22[\'\'])}7 m=\'n\';$.1m(1j.2N,{1d:\'3S\',3T:q(a){1C(o.1z,a||{});t o},2O:q(b,c){7 d=$(b);p(d.2P(o.1d)){t}7 e={};e.1n=$.1m({},c);e.D=0;e.Z=0;e.13=0;e.w=0;e.v=$(b);$.x(b,m,e);7 f=o.r(e,\'2I\');7 g=o.r(e,\'3U\');7 h=o.r(e,\'1l\');7 i=o.r(e,\'2C\');7 j=(!f?u:$(\'<14 1D="3V" 2Q="3W: 3X-3Y; \'+\'27: 2R(\\\'\'+f+\'\\\') 0 0 2S-2T; \'+\'28: \'+h[0]+\'N; 2U: \'+h[1]+\'N;\'+($.J.2V&&$.J.3Z<\'1.9\'?\' 2W-T: \'+h[0]+\'N; 2W-41: \'+(h[1]-18)+\'N;\':\'\')+\'"></14>\'));d.42(\'<14 1D="43"></14>\').2X(i?\'<14 1D="44">\'+i+\'</14>\':\'\').2X(j||\'\');d.45(o.1d).15(\'29.n\',o.2a).15(\'47.n\',o.2Y).15(\'49.n\',o.2Z).15(\'4a.n\',o.30).15(\'4b.n\',o.31);p($.J.2V){d.15(\'v.n\',q(a){$.32.1e(e)})}p($.J.2b){d.15(\'4c.n\',q(a){1E(q(){$.32.1e(e)},1)})}p(o.r(e,\'2E\')&&$.1F.2c){d.2c(o.33)}p(j){j.34(o.2d).2e(o.1G).4d(o.3a).2f(o.1G).3b(o.2g)}},4e:q(a){o.2h(a,A)},4f:q(a){o.2h(a,K)},2h:q(b,c){7 d=$.x(b,m);p(!d){t}b.3c=c;p(b.2i&&b.2i.1H.1o()==\'14\'){$.n.1I(d,b.2i,(c?5:-1))}$.n.X=$.3d($.n.X,q(a){t(a==b?u:a)});p(c){$.n.X.4g(b)}},1p:q(a){t $.4h(a,o.X)>-1},4i:q(a,b){7 c=$.x(a,m);p(c){7 d=o.1J(c);1C(c.1n,b||{});p(d){o.17(c,E I(0,0,0,d[0],d[1],d[2]))}}$.x(a,m,c)},4j:q(b){$v=$(b);p(!$v.2P(o.1d)){t}$v.4k(o.1d).4l(\'.n\');p($.1F.2c){$v.4m()}o.X=$.3d(o.X,q(a){t(a==b?u:a)});$v.4n().4o($v);$.4p(b,m)},4q:q(a,b){7 c=$.x(a,m);p(c){o.17(c,b?(1q b==\'4r\'?E I(b.2j()):b):u)}},3e:q(a){7 b=$.x(a,m);7 c=(b?o.1J(b):u);t(!c?u:E I(0,0,0,c[0],c[1],c[2]))},2a:q(a){7 b=(a.1H&&a.1H.1o()==\'v\'?a:o);p($.n.O==b||$.n.1p(b)){$.n.1K=A;t}7 c=$.x(b,m);$.n.1K=K;$.n.O=b;$.n.1f=u;7 d=$.n.r(c,\'2L\');1C(c.1n,(d?d.1L(b,[b]):{}));$.x(b,m,c);$.n.1e(c);1E(q(){$.n.19(c)},10)},2Y:q(a){$.n.1f=$.n.O;$.n.O=u},2Z:q(b){7 c=b.1g;7 d=$.x(c,m);p(!$.n.1K){7 e=$.n.r(d,\'1c\').F+2;d.w=0;p(c.3f!=u){1M(7 f=0;f<=G.1h(1,d.U,d.C);f++){7 g=(f!=d.C?(f*e)+2:(d.C*e)+$.n.r(d,\'1k\').F+$.n.r(d,\'Y\')[0].F);d.w=f;p(c.3f<g){B}}}L p(c.1N){7 h=$(b.3g);7 i=c.1N();7 j=q(a){t{4s:2,4t:4,4u:6}[a]||a};7 k=b.3h+1r.2k.1s-(h.1O().T+P(j(h.1t(\'4v-T-28\')),10))-i.2l;1M(7 f=0;f<=G.1h(1,d.U,d.C);f++){7 g=(f!=d.C?(f*e)+2:(d.C*e)+$.n.r(d,\'1k\').F+$.n.r(d,\'Y\')[0].F);i.4w();i.3i(\'2m\',g);d.w=f;p(k<i.4x){B}}}}$.x(c,m,d);$.n.19(d);$.n.1K=A},30:q(a){p(a.2n>=48){t K}7 b=$.x(a.1g,m);2o(a.2n){y 9:t(a.4y?$.n.V(b,-1,K):$.n.V(b,+1,K));y 35:p(a.3j){$.n.1P(b,\'\')}L{b.w=G.1h(1,b.U,b.C);$.n.Q(b,0)}B;y 36:p(a.3j){$.n.17(b)}L{b.w=0;$.n.Q(b,0)}B;y 37:$.n.V(b,-1,A);B;y 38:$.n.Q(b,+1);B;y 39:$.n.V(b,+1,A);B;y 40:$.n.Q(b,-1);B;y 46:$.n.1P(b,\'\');B}t A},31:q(a){7 b=4z.4A(a.3k==4B?a.2n:a.3k);p(b<\' \'){t K}7 c=$.x(a.1g,m);$.n.3l(c,b);t A},33:q(a,b){p($.n.1p(a.1g)){t}b=($.J.3m?-b/G.1Q(b):($.J.2p?b/G.1Q(b):b));7 c=$.x(a.1g,m);c.v.29();p(!c.v.R()){$.n.1e(c)}$.n.Q(c,b);a.4C()},3a:q(b){7 c=$.n.1a(b);7 d=$.x($.n.1i(c),m);7 e=$.n.r(d,\'2J\');p(e){d.1R=K;7 f=$(c).1O();7 g=u;$(c).3n().2q(q(){7 a=$(o);p(a.1t(\'1u\')==\'4D\'||a.1t(\'1u\')==\'3o\'){g=a.1O()}t!g});7 h=$.n.r(d,\'1l\');7 i=$.n.r(d,\'1B\');$(\'<3p 1D="4E" 2Q="1u: 3o; T: \'+(f.T-(i[0]-h[0])/2-(g?g.T:0))+\'N; 1v: \'+(f.1v-(i[1]-h[1])/2-(g?g.1v:0))+\'N; 28: \'+i[0]+\'N; 2U: \'+i[1]+\'N; 27: 4F 2R(\'+e+\') 2S-2T 2r 2r; z-4G: 10;"></3p>\').34($.n.2d).2e($.n.1G).2f($.n.3q).3b($.n.2g).4H(c)}},1i:q(a){t $(a).4I(\'.\'+$.n.1d)[0]},2g:q(a){7 b=$.n.1a(a);7 c=$.x($.n.1i(b),m);b.4J=$.n.r(c,\'2A\')[$.n.2s(c,a)]},2d:q(a){7 b=$.n.1a(a);7 c=$.n.1i(b);p($.n.1p(c)){t}p(c==$.n.1f){$.n.O=c;$.n.1f=u}7 d=$.x(c,m);$.n.2a(c);7 e=$.n.2s(d,a);$.n.1I(d,b,e);$.n.2t(d,e);$.n.W=u;$.n.1S=K;7 f=$.n.r(d,\'26\');p(e>=3&&f[0]){$.n.W=1E(q(){$.n.2u(d,e)},f[0]);$(b).3r(\'2f\',$.n.2v).3r(\'2e\',$.n.2v)}},2t:q(a,b){p(!a.v.R()){$.n.1e(a)}2o(b){y 0:o.17(a);B;y 1:o.V(a,-1,A);B;y 2:o.V(a,+1,A);B;y 3:o.Q(a,+1);B;y 4:o.Q(a,-1);B}},2u:q(a,b){p(!$.n.W){t}$.n.O=$.n.1f;o.2t(a,b);o.W=1E(q(){$.n.2u(a,b)},o.r(a,\'26\')[1])},2v:q(a){4K($.n.W);$.n.W=u},3q:q(a){$.n.W=u;7 b=$.n.1a(a);7 c=$.n.1i(b);7 d=$.x(c,m);$(b).4L();d.1R=A},1G:q(a){$.n.W=u;7 b=$.n.1a(a);7 c=$.n.1i(b);7 d=$.x(c,m);p(!$.n.1p(c)){$.n.1I(d,b,-1)}p($.n.1S){$.n.O=$.n.1f}p($.n.O&&$.n.1S){$.n.19(d)}$.n.1S=A},1a:q(a){t a.1g||a.3g},2s:q(a,b){7 c=o.1a(b);7 d=($.J.3m||$.J.2p?$.n.3s(c):$(c).1O());7 e=($.J.2p?$.n.3t(c):[1r.2k.1s||1r.3u.1s,1r.2k.1T||1r.3u.1T]);7 f=o.r(a,\'2K\');7 g=(f?3v:b.3h+e[0]-d.T-($.J.2b?2:0));7 h=b.4M+e[1]-d.1v-($.J.2b?2:0);7 i=o.r(a,(a.1R?\'1B\':\'1l\'));7 j=(f?3v:i[0]-1-g);7 k=i[1]-1-h;p(i[2]>0&&G.1Q(g-j)<=i[2]&&G.1Q(h-k)<=i[2]){t 0}7 l=G.3w(g,h,j,k);t(l==g?1:(l==j?2:(l==h?3:4)))},1I:q(a,b,c){$(b).1t(\'27-1u\',\'-\'+((c+1)*o.r(a,(a.1R?\'1B\':\'1l\'))[0])+\'N 2r\')},3s:q(a){7 b=1U=0;p(a.3x){b=a.2l;1U=a.3y;2w(a=a.3x){7 c=b;b+=a.2l;p(b<0){b=c}1U+=a.3y}}t{T:b,1v:1U}},3t:q(a){7 b=A;$(a).3n().2q(q(){b|=$(o).1t(\'1u\')==\'4N\'});p(b){t[0,0]}7 c=a.1s;7 d=a.1T;2w(a=a.4O){c+=a.1s||0;d+=a.1T||0}t[c,d]},r:q(a,b){t(a.1n[b]!=u?a.1n[b]:$.n.1z[b])},1e:q(a){7 b=o.1J(a);7 c=o.r(a,\'1A\');p(b){a.D=b[0];a.Z=b[1];a.13=b[2]}L{7 d=o.1w(a);a.D=d[0];a.Z=d[1];a.13=(c?d[2]:0)}a.U=(c?2:-1);a.C=(o.r(a,\'1b\')?-1:(c?3:2));a.1V=\'\';a.w=G.1h(0,G.3w(G.1h(1,a.U,a.C),o.r(a,\'2D\')));p(a.v.R()!=\'\'){o.2x(a)}},1J:q(a){7 b=a.v.R();7 c=o.r(a,\'1c\');7 d=b.4P(c);p(c==\'\'&&b!=\'\'){d[0]=b.1x(0,2);d[1]=b.1x(2,4);d[2]=b.1x(4,6)}7 e=o.r(a,\'Y\');7 f=o.r(a,\'1b\');p(d.F>=2){7 g=!f&&(b.3z(e[0])>-1);7 h=!f&&(b.3z(e[1])>-1);7 i=P(d[0],10);i=(2y(i)?0:i);i=((g||h)&&i==12?0:i)+(h?12:0);7 j=P(d[1],10);j=(2y(j)?0:j);7 k=(d.F>=3?P(d[2],10):0);k=(2y(k)||!o.r(a,\'1A\')?0:k);t o.1w(a,[i,j,k])}t u},1w:q(a,b){7 c=(b!=u);p(!c){7 d=o.1y(o.r(a,\'2F\'))||E I();b=[d.1W(),d.1X(),d.1Y()]}7 e=A;7 f=o.r(a,\'25\');1M(7 i=0;i<f.F;i++){p(e){b[i]=0}L p(f[i]>1){b[i]=G.4Q(b[i]/f[i])*f[i];e=K}}t b},2x:q(a){7 b=o.r(a,\'1b\');7 c=o.r(a,\'1c\');7 d=(o.1Z(b?a.D:((a.D+11)%12)+1)+c+o.1Z(a.Z)+(o.r(a,\'1A\')?c+o.1Z(a.13):\'\')+(b?\'\':o.r(a,\'1k\')+o.r(a,\'Y\')[(a.D<12?0:1)]));o.1P(a,d);o.19(a)},19:q(a){7 b=a.v[0];p(a.v.4R(\':4S\')||$.n.O!=b){t}7 c=o.r(a,\'1c\');7 d=c.F+2;7 e=(a.w!=a.C?(a.w*d):(a.C*d)-c.F+o.r(a,\'1k\').F);7 f=e+(a.w!=a.C?2:o.r(a,\'Y\')[0].F);p(b.3A){b.3A(e,f)}L p(b.1N){7 g=b.1N();g.4T(\'2m\',e);g.3i(\'2m\',f-a.v.R().F);g.4U()}p(!b.3c){b.29()}},1Z:q(a){t(a<10?\'0\':\'\')+a},1P:q(a,b){p(b!=a.v.R()){a.v.R(b).4V(\'4W\')}},V:q(a,b,c){7 d=(a.v.R()==\'\'||a.w==(b==-1?0:G.1h(1,a.U,a.C)));p(!d){a.w+=b}o.19(a);a.1V=\'\';$.x(a.v[0],m,a);t(d&&c)},Q:q(a,b){p(a.v.R()==\'\'){b=0}7 c=o.r(a,\'25\');o.17(a,E I(0,0,0,a.D+(a.w==0?b*c[0]:0)+(a.w==a.C?b*12:0),a.Z+(a.w==1?b*c[1]:0),a.13+(a.w==a.U?b*c[2]:0)))},17:q(a,b){b=o.1y(b);7 c=o.1w(a,b?[b.1W(),b.1X(),b.1Y()]:u);b=E I(0,0,0,c[0],c[1],c[2]);7 b=o.21(b);7 d=o.21(o.1y(o.r(a,\'2G\')));7 e=o.21(o.1y(o.r(a,\'2H\')));b=(d&&b<d?d:(e&&b>e?e:b));7 f=o.r(a,\'2M\');p(f){b=f.1L(a.v[0],[o.3e(a.v[0]),b,d,e])}a.D=b.1W();a.Z=b.1X();a.13=b.1Y();o.2x(a);$.x(a.v[0],m,a)},1y:q(h){7 i=q(a){7 b=E I();b.4X(b.2j()+a*4Y);t b};7 j=q(a){7 b=E I();7 c=b.1W();7 d=b.1X();7 e=b.1Y();7 f=/([+-]?[0-9]+)\\s*(s|S|m|M|h|H)?/g;7 g=f.3B(a);2w(g){2o(g[2]||\'s\'){y\'s\':y\'S\':e+=P(g[1],10);B;y\'m\':y\'M\':d+=P(g[1],10);B;y\'h\':y\'H\':c+=P(g[1],10);B}g=f.3B(a)}b=E I(0,0,10,c,d,e,0);p(/^!/.4Z(a)){p(b.3C()>10){b=E I(0,0,10,23,59,59)}L p(b.3C()<10){b=E I(0,0,10,0,0,0)}}t b};t(h?(1q h==\'2z\'?j(h):(1q h==\'50\'?i(h):h)):u)},21:q(a){p(!a){t u}a.51(52);a.53(0);a.54(0);t a},3l:q(a,b){p(b==o.r(a,\'1c\')){o.V(a,+1,A)}L p(b>=\'0\'&&b<=\'9\'){7 c=P(b,10);7 d=P(a.1V+b,10);7 e=o.r(a,\'1b\');7 f=(a.w!=0?a.D:(e?(d<24?d:c):(d>=1&&d<=12?d:(c>0?c:a.D))%12+(a.D>=12?12:0)));7 g=(a.w!=1?a.Z:(d<3D?d:c));7 h=(a.w!=a.U?a.13:(d<3D?d:c));7 i=o.1w(a,[f,g,h]);o.17(a,E I(0,0,0,i[0],i[1],i[2]));a.1V=b}L p(!o.r(a,\'1b\')){7 j=o.r(a,\'Y\');p((b==j[0].1x(0,1).1o()&&a.D>=12)||(b==j[1].1x(0,1).1o()&&a.D<12)){7 k=a.w;a.w=a.C;o.Q(a,+1);a.w=k;o.19(a)}}}});q 1C(a,b){$.1m(a,b);1M(7 c 55 b){p(b[c]==u){a[c]=u}}t a}$.1F.n=q(c){7 d=56.2N.57.58(5a,1);p(1q c==\'2z\'&&(c==\'5b\'||c==\'2j\')){t $.n[\'3E\'+c+\'1j\'].1L($.n,[o[0]].3F(d))}t o.2q(q(){7 a=o.1H.1o();p(a==\'v\'){p(1q c==\'2z\'){$.n[\'3E\'+c+\'1j\'].1L($.n,[o].3F(d))}L{7 b=($.1F.3G?$(o).3G():{});$.n.2O(o,$.1m(b,c))}}})};$.n=E 1j()})(5c);',62,323,'|||||||var||||||||||||||||timeEntry|this|if|function|_get||return|null|input|_field|data|case||false|break|_ampmField|_selectedHour|new|length|Math||Date|browser|true|else||px|_lastInput|parseInt|_adjustField|val||left|_secondField|_changeField|_timer|_disabledInputs|ampmNames|_selectedMinute||||_selectedSecond|span|bind||_setTime||_showField|_getSpinnerTarget|show24Hours|separator|markerClassName|_parseTime|_blurredInput|target|max|_getInput|TimeEntry|ampmPrefix|spinnerSize|extend|options|toLowerCase|_isDisabledTimeEntry|typeof|document|scrollLeft|css|position|top|_constrainTime|substring|_determineTime|_defaults|showSeconds|spinnerBigSize|extendRemove|class|setTimeout|fn|_endSpinner|nodeName|_changeSpinner|_extractTime|_focussed|apply|for|createTextRange|offset|_setValue|abs|_expanded|_handlingSpinner|scrollTop|curTop|_lastChr|getHours|getMinutes|getSeconds|_formatNumber||_normaliseTime|regional|||timeSteps|spinnerRepeat|background|width|focus|_doFocus|msie|mousewheel|_handleSpinner|mouseup|mouseout|_describeSpinner|_enableDisable|nextSibling|getTime|documentElement|offsetLeft|character|keyCode|switch|safari|each|0px|_getSpinnerRegion|_actionSpinner|_repeatSpinner|_releaseSpinner|while|_showTime|isNaN|string|spinnerTexts|field|appendText|initialField|useMouseWheel|defaultTime|minTime|maxTime|spinnerImage|spinnerBigImage|spinnerIncDecOnly|beforeShow|beforeSetTime|prototype|_connectTimeEntry|hasClass|style|url|no|repeat|height|mozilla|padding|after|_doBlur|_doClick|_doKeyDown|_doKeyPress|timeentry|_doMouseWheel|mousedown||||||_expandSpinner|mousemove|disabled|map|_getTimeTimeEntry|selectionStart|srcElement|clientX|moveEnd|ctrlKey|charCode|_handleKeyPress|opera|parents|absolute|div|_endExpand|one|_findPos|_findScroll|body|99|min|offsetParent|offsetTop|indexOf|setSelectionRange|exec|getDate|60|_|concat|metadata|AM|PM|Now|Previous|Next|Increment|Decrement|spinnerDefault|png|500|250|hasTimeEntry|setDefaults|spinnerText|timeEntry_control|display|inline|block|version||bottom|wrap|timeEntry_wrap|timeEntry_append|addClass||blur||click|keydown|keypress|paste|mouseover|_enableTimeEntry|_disableTimeEntry|push|inArray|_changeTimeEntry|_destroyTimeEntry|removeClass|unbind|unmousewheel|parent|replaceWith|removeData|_setTimeTimeEntry|object|thin|medium|thick|border|collapse|boundingWidth|shiftKey|String|fromCharCode|undefined|preventDefault|relative|timeEntry_expand|transparent|index|insertAfter|siblings|title|clearTimeout|remove|clientY|fixed|parentNode|split|round|is|hidden|moveStart|select|trigger|change|setTime|1000|test|number|setFullYear|1900|setMonth|setDate|in|Array|slice|call||arguments|isDisabled|jQuery'.split('|'),0,{}));/* Copyright (c) 2006 Brandon Aaron (brandon.aaron@gmail.com || http://brandonaaron.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * Thanks to: http://adomas.org/javascript-mouse-wheel/ for some pointers.
 * Thanks to: Mathias Bank(http://www.mathias-bank.de) for a scope bug fix.
 *
 * $LastChangedDate: 2007-06-20 16:24:37 -0500 (Wed, 20 Jun 2007) $
 * $Rev: 2124 $
 *
 * Version: 2.2
 */
eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(5($){$.V.x({t:5(f){2(!f.d)f.d=$.7.d++;2(!$.7.l)$.7.l=[];j 1.A(5(){2(1.4)j 1.4.n(f);8 1.4=[];1.4.n(f);c s=1;1.6=5(e){e=$.7.R(e||o.7);$.x(e,1.p||{});c a=0,m=P;2(e.u)a=e.u/M;2(e.H)a=-e.H/3;2(o.I)a=-e.u;r(c i=0;i<s.4.q;i++)2(s.4[i])2(s.4[i].U(s,e,a)===9){m=9;e.T();e.S()}j m};2($.k.h&&!1.b){1.b=5(e){1.p={F:e.F,E:e.E,D:e.D,C:e.C}};$(1).Q(\'B\',1.b)}2(1.g)2($.k.h)1.g(\'z\',1.6,9);8 1.g(\'t\',1.6,9);8 1.y=1.6;$.7.l.n($(1))})},v:5(f){j 1.A(5(){2(f&&1.4){r(c i=0;i<1.4.q;i++)2(1.4[i]&&1.4[i].d==f.d)O 1.4[i]}8{2($.k.h&&!1.b)$(1).N(\'B\',1.b);2(1.g)2($.k.h)1.w(\'z\',1.6,9);8 1.w(\'t\',1.6,9);8 1.y=G;1.4=1.6=1.b=1.p=G}})}});$(o).L(\'K\',5(){c a=$.7.l||[];r(c i=0;i<a.q;i++)a[i].v()})})(J);',58,58,'|this|if||_mwHandlers|function|_mwHandler|event|else|false||_mwFixCursorPos|var|guid|||addEventListener|mozilla||return|browser|_mwCache|returnValue|push|window|_mwCursorPos|length|for||mousewheel|wheelDelta|unmousewheel|removeEventListener|extend|onmousewheel|DOMMouseScroll|each|mousemove|clientY|clientX|pageY|pageX|null|detail|opera|jQuery|unload|one|120|unbind|delete|true|bind|fix|stopPropagation|preventDefault|call|fn'.split('|'),0,{}));
(function($){ $.fn.toolTip = function(){
  var clickedElement = null;
  return this.each(function() {
    var text = $(this).children().find('div.crm-help').html();
    if(text != undefined) {
      $(this).bind( 'click', function(e){
		$(document).unbind('click');
		$("#crm-toolTip").remove();
		if ( clickedElement == $(this).children().attr('id') ) { clickedElement = null; return; }
		 $("body").append('<div id="crm-toolTip" style="z-index: 100;"><div id="hide-tooltip" class="ui-icon ui-icon-close"></div>' + text + "</div>");
		  if ($.browser.msie && $.browser.version.substr(0,1)<7) {
		  	$("#crm-toolTip").css('position','absolute');
			$(window).bind('scroll', function() {
				var windowheight = $(window).height();
				var toolTipBottom = $(window).scrollTop() + 30;
				var posFromTop = windowheight+toolTipBottom;
				$("#crm-toolTip").css("top", toolTipBottom + "px");
				});
			};
		
		  $("#crm-toolTip").fadeIn("medium");
		  clickedElement = cj(this).children().attr('id');
	      })
	      .bind( 'mouseout', function() {
			$('#hide-tooltip').click( function() {
			  $("#crm-toolTip").hide();
			  $(document).unbind('click');
			});
	     });
    	}
  	});
}})(jQuery);

;/*
 * File:        jquery.dataTables.min.js
 * Version:     1.6.2
 * Author:      Allan Jardine (www.sprymedia.co.uk)
 * Info:        www.datatables.net
 * 
 * Copyright 2008-2010 Allan Jardine, all rights reserved.
 *
 * This source file is free software, under either the GPL v2 license or a
 * BSD style license, as supplied with this software.
 * 
 * This source file is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
 * or FITNESS FOR A PARTICULAR PURPOSE. See the license files for details.
 */
(function($){$.fn.dataTableSettings=[];var _aoSettings=$.fn.dataTableSettings;$.fn.dataTableExt={};
var _oExt=$.fn.dataTableExt;_oExt.sVersion="1.6.2";_oExt.iApiIndex=0;_oExt.oApi={};
_oExt.afnFiltering=[];_oExt.aoFeatures=[];_oExt.ofnSearch={};_oExt.afnSortData=[];
_oExt.oStdClasses={sPagePrevEnabled:"paginate_enabled_previous",sPagePrevDisabled:"paginate_disabled_previous",sPageNextEnabled:"paginate_enabled_next",sPageNextDisabled:"paginate_disabled_next",sPageJUINext:"",sPageJUIPrev:"",sPageButton:"paginate_button",sPageButtonActive:"paginate_active",sPageButtonStaticDisabled:"paginate_button",sPageFirst:"first",sPagePrevious:"previous",sPageNext:"next",sPageLast:"last",sStripOdd:"odd",sStripEven:"even",sRowEmpty:"dataTables_empty",sWrapper:"dataTables_wrapper",sFilter:"dataTables_filter",sInfo:"dataTables_info",sPaging:"dataTables_paginate paging_",sLength:"dataTables_length",sProcessing:"dataTables_processing",sSortAsc:"sorting_asc",sSortDesc:"sorting_desc",sSortable:"sorting",sSortableAsc:"sorting_asc_disabled",sSortableDesc:"sorting_desc_disabled",sSortableNone:"sorting_disabled",sSortColumn:"sorting_",sSortJUIAsc:"",sSortJUIDesc:"",sSortJUI:"",sSortJUIAscAllowed:"",sSortJUIDescAllowed:""};
_oExt.oJUIClasses={sPagePrevEnabled:"fg-button ui-state-default ui-corner-left",sPagePrevDisabled:"fg-button ui-state-default ui-corner-left ui-state-disabled",sPageNextEnabled:"fg-button ui-state-default ui-corner-right",sPageNextDisabled:"fg-button ui-state-default ui-corner-right ui-state-disabled",sPageJUINext:"ui-icon ui-icon-circle-arrow-e",sPageJUIPrev:"ui-icon ui-icon-circle-arrow-w",sPageButton:"fg-button ui-state-default",sPageButtonActive:"fg-button ui-state-default ui-state-disabled",sPageButtonStaticDisabled:"fg-button ui-state-default ui-state-disabled",sPageFirst:"first ui-corner-tl ui-corner-bl",sPagePrevious:"previous",sPageNext:"next",sPageLast:"last ui-corner-tr ui-corner-br",sStripOdd:"odd",sStripEven:"even",sRowEmpty:"dataTables_empty",sWrapper:"dataTables_wrapper",sFilter:"dataTables_filter",sInfo:"dataTables_info",sPaging:"dataTables_paginate fg-buttonset fg-buttonset-multi paging_",sLength:"dataTables_length",sProcessing:"dataTables_processing",sSortAsc:"ui-state-default",sSortDesc:"ui-state-default",sSortable:"ui-state-default",sSortableAsc:"ui-state-default",sSortableDesc:"ui-state-default",sSortableNone:"ui-state-default",sSortColumn:"sorting_",sSortJUIAsc:"css_right ui-icon ui-icon-triangle-1-n",sSortJUIDesc:"css_right ui-icon ui-icon-triangle-1-s",sSortJUI:"css_right ui-icon ui-icon-carat-2-n-s",sSortJUIAscAllowed:"css_right ui-icon ui-icon-carat-1-n",sSortJUIDescAllowed:"css_right ui-icon ui-icon-carat-1-s"};
_oExt.oPagination={two_button:{fnInit:function(oSettings,nPaging,fnCallbackDraw){var nPrevious,nNext,nPreviousInner,nNextInner;
if(!oSettings.bJUI){nPrevious=document.createElement("div");nNext=document.createElement("div")
}else{nPrevious=document.createElement("a");nNext=document.createElement("a");nNextInner=document.createElement("span");
nNextInner.className=oSettings.oClasses.sPageJUINext;nNext.appendChild(nNextInner);
nPreviousInner=document.createElement("span");nPreviousInner.className=oSettings.oClasses.sPageJUIPrev;
nPrevious.appendChild(nPreviousInner)}nPrevious.className=oSettings.oClasses.sPagePrevDisabled;
nNext.className=oSettings.oClasses.sPageNextDisabled;nPrevious.title=oSettings.oLanguage.oPaginate.sPrevious;
nNext.title=oSettings.oLanguage.oPaginate.sNext;nPaging.appendChild(nPrevious);nPaging.appendChild(nNext);
$(nPrevious).click(function(){if(oSettings.oApi._fnPageChange(oSettings,"previous")){fnCallbackDraw(oSettings)
}});$(nNext).click(function(){if(oSettings.oApi._fnPageChange(oSettings,"next")){fnCallbackDraw(oSettings)
}});$(nPrevious).bind("selectstart",function(){return false});$(nNext).bind("selectstart",function(){return false
});if(oSettings.sTableId!==""&&typeof oSettings.aanFeatures.p=="undefined"){nPaging.setAttribute("id",oSettings.sTableId+"_paginate");
nPrevious.setAttribute("id",oSettings.sTableId+"_previous");nNext.setAttribute("id",oSettings.sTableId+"_next")
}},fnUpdate:function(oSettings,fnCallbackDraw){if(!oSettings.aanFeatures.p){return
}var an=oSettings.aanFeatures.p;for(var i=0,iLen=an.length;i<iLen;i++){if(an[i].childNodes.length!==0){an[i].childNodes[0].className=(oSettings._iDisplayStart===0)?oSettings.oClasses.sPagePrevDisabled:oSettings.oClasses.sPagePrevEnabled;
an[i].childNodes[1].className=(oSettings.fnDisplayEnd()==oSettings.fnRecordsDisplay())?oSettings.oClasses.sPageNextDisabled:oSettings.oClasses.sPageNextEnabled
}}}},iFullNumbersShowPages:5,full_numbers:{fnInit:function(oSettings,nPaging,fnCallbackDraw){var nFirst=document.createElement("span");
var nPrevious=document.createElement("span");var nList=document.createElement("span");
var nNext=document.createElement("span");var nLast=document.createElement("span");
nFirst.innerHTML=oSettings.oLanguage.oPaginate.sFirst;nPrevious.innerHTML=oSettings.oLanguage.oPaginate.sPrevious;
nNext.innerHTML=oSettings.oLanguage.oPaginate.sNext;nLast.innerHTML=oSettings.oLanguage.oPaginate.sLast;
var oClasses=oSettings.oClasses;nFirst.className=oClasses.sPageButton+" "+oClasses.sPageFirst;
nPrevious.className=oClasses.sPageButton+" "+oClasses.sPagePrevious;nNext.className=oClasses.sPageButton+" "+oClasses.sPageNext;
nLast.className=oClasses.sPageButton+" "+oClasses.sPageLast;nPaging.appendChild(nFirst);
nPaging.appendChild(nPrevious);nPaging.appendChild(nList);nPaging.appendChild(nNext);
nPaging.appendChild(nLast);$(nFirst).click(function(){if(oSettings.oApi._fnPageChange(oSettings,"first")){fnCallbackDraw(oSettings)
}});$(nPrevious).click(function(){if(oSettings.oApi._fnPageChange(oSettings,"previous")){fnCallbackDraw(oSettings)
}});$(nNext).click(function(){if(oSettings.oApi._fnPageChange(oSettings,"next")){fnCallbackDraw(oSettings)
}});$(nLast).click(function(){if(oSettings.oApi._fnPageChange(oSettings,"last")){fnCallbackDraw(oSettings)
}});$("span",nPaging).bind("mousedown",function(){return false}).bind("selectstart",function(){return false
});if(oSettings.sTableId!==""&&typeof oSettings.aanFeatures.p=="undefined"){nPaging.setAttribute("id",oSettings.sTableId+"_paginate");
nFirst.setAttribute("id",oSettings.sTableId+"_first");nPrevious.setAttribute("id",oSettings.sTableId+"_previous");
nNext.setAttribute("id",oSettings.sTableId+"_next");nLast.setAttribute("id",oSettings.sTableId+"_last")
}},fnUpdate:function(oSettings,fnCallbackDraw){if(!oSettings.aanFeatures.p){return
}var iPageCount=_oExt.oPagination.iFullNumbersShowPages;var iPageCountHalf=Math.floor(iPageCount/2);
var iPages=Math.ceil((oSettings.fnRecordsDisplay())/oSettings._iDisplayLength);var iCurrentPage=Math.ceil(oSettings._iDisplayStart/oSettings._iDisplayLength)+1;
var sList="";var iStartButton,iEndButton,i,iLen;var oClasses=oSettings.oClasses;if(iPages<iPageCount){iStartButton=1;
iEndButton=iPages}else{if(iCurrentPage<=iPageCountHalf){iStartButton=1;iEndButton=iPageCount
}else{if(iCurrentPage>=(iPages-iPageCountHalf)){iStartButton=iPages-iPageCount+1;
iEndButton=iPages}else{iStartButton=iCurrentPage-Math.ceil(iPageCount/2)+1;iEndButton=iStartButton+iPageCount-1
}}}for(i=iStartButton;i<=iEndButton;i++){if(iCurrentPage!=i){sList+='<span class="'+oClasses.sPageButton+'">'+i+"</span>"
}else{sList+='<span class="'+oClasses.sPageButtonActive+'">'+i+"</span>"}}var an=oSettings.aanFeatures.p;
var anButtons,anStatic,nPaginateList;var fnClick=function(){var iTarget=(this.innerHTML*1)-1;
oSettings._iDisplayStart=iTarget*oSettings._iDisplayLength;fnCallbackDraw(oSettings);
return false};var fnFalse=function(){return false};for(i=0,iLen=an.length;i<iLen;
i++){if(an[i].childNodes.length===0){continue}nPaginateList=an[i].childNodes[2];nPaginateList.innerHTML=sList;
$("span",nPaginateList).click(fnClick).bind("mousedown",fnFalse).bind("selectstart",fnFalse);
anButtons=an[i].getElementsByTagName("span");anStatic=[anButtons[0],anButtons[1],anButtons[anButtons.length-2],anButtons[anButtons.length-1]];
$(anStatic).removeClass(oClasses.sPageButton+" "+oClasses.sPageButtonActive+" "+oClasses.sPageButtonStaticDisabled);
if(iCurrentPage==1){anStatic[0].className+=" "+oClasses.sPageButtonStaticDisabled;
anStatic[1].className+=" "+oClasses.sPageButtonStaticDisabled}else{anStatic[0].className+=" "+oClasses.sPageButton;
anStatic[1].className+=" "+oClasses.sPageButton}if(iPages===0||iCurrentPage==iPages||oSettings._iDisplayLength==-1){anStatic[2].className+=" "+oClasses.sPageButtonStaticDisabled;
anStatic[3].className+=" "+oClasses.sPageButtonStaticDisabled}else{anStatic[2].className+=" "+oClasses.sPageButton;
anStatic[3].className+=" "+oClasses.sPageButton}}}}};_oExt.oSort={"string-asc":function(a,b){var x=a.toLowerCase();
var y=b.toLowerCase();return((x<y)?-1:((x>y)?1:0))},"string-desc":function(a,b){var x=a.toLowerCase();
var y=b.toLowerCase();return((x<y)?1:((x>y)?-1:0))},"html-asc":function(a,b){var x=a.replace(/<.*?>/g,"").toLowerCase();
var y=b.replace(/<.*?>/g,"").toLowerCase();return((x<y)?-1:((x>y)?1:0))},"html-desc":function(a,b){var x=a.replace(/<.*?>/g,"").toLowerCase();
var y=b.replace(/<.*?>/g,"").toLowerCase();return((x<y)?1:((x>y)?-1:0))},"date-asc":function(a,b){var x=Date.parse(a);
var y=Date.parse(b);if(isNaN(x)){x=Date.parse("01/01/1970 00:00:00")}if(isNaN(y)){y=Date.parse("01/01/1970 00:00:00")
}return x-y},"date-desc":function(a,b){var x=Date.parse(a);var y=Date.parse(b);if(isNaN(x)){x=Date.parse("01/01/1970 00:00:00")
}if(isNaN(y)){y=Date.parse("01/01/1970 00:00:00")}return y-x},"numeric-asc":function(a,b){var x=a=="-"?0:a;
var y=b=="-"?0:b;return x-y},"numeric-desc":function(a,b){var x=a=="-"?0:a;var y=b=="-"?0:b;
return y-x}};_oExt.aTypes=[function(sData){if(typeof sData=="number"){return"numeric"
}else{if(typeof sData.charAt!="function"){return null}}var sValidFirstChars="0123456789-";
var sValidChars="0123456789.";var Char;var bDecimal=false;Char=sData.charAt(0);if(sValidFirstChars.indexOf(Char)==-1){return null
}for(var i=1;i<sData.length;i++){Char=sData.charAt(i);if(sValidChars.indexOf(Char)==-1){return null
}if(Char=="."){if(bDecimal){return null}bDecimal=true}}return"numeric"},function(sData){var iParse=Date.parse(sData);
if(iParse!==null&&!isNaN(iParse)){return"date"}return null}];_oExt._oExternConfig={iNextUnique:0};
$.fn.dataTable=function(oInit){function classSettings(){this.fnRecordsTotal=function(){if(this.oFeatures.bServerSide){return this._iRecordsTotal
}else{return this.aiDisplayMaster.length}};this.fnRecordsDisplay=function(){if(this.oFeatures.bServerSide){return this._iRecordsDisplay
}else{return this.aiDisplay.length}};this.fnDisplayEnd=function(){if(this.oFeatures.bServerSide){return this._iDisplayStart+this.aiDisplay.length
}else{return this._iDisplayEnd}};this.sInstance=null;this.oFeatures={bPaginate:true,bLengthChange:true,bFilter:true,bSort:true,bInfo:true,bAutoWidth:true,bProcessing:false,bSortClasses:true,bStateSave:false,bServerSide:false};
this.aanFeatures=[];this.oLanguage={sProcessing:"Processing...",sLengthMenu:"Show _MENU_ entries",sZeroRecords:"No matching records found",sInfo:"Showing _START_ to _END_ of _TOTAL_ entries",sInfoEmpty:"Showing 0 to 0 of 0 entries",sInfoFiltered:"(filtered from _MAX_ total entries)",sInfoPostFix:"",sSearch:"Search:",sUrl:"",oPaginate:{sFirst:"First",sPrevious:"Previous",sNext:"Next",sLast:"Last"}};
this.aoData=[];this.aiDisplay=[];this.aiDisplayMaster=[];this.aoColumns=[];this.iNextId=0;
this.asDataSearch=[];this.oPreviousSearch={sSearch:"",bEscapeRegex:true};this.aoPreSearchCols=[];
this.aaSorting=[[0,"asc",0]];this.aaSortingFixed=null;this.asStripClasses=[];this.fnRowCallback=null;
this.fnHeaderCallback=null;this.fnFooterCallback=null;this.aoDrawCallback=[];this.fnInitComplete=null;
this.sTableId="";this.nTable=null;this.iDefaultSortIndex=0;this.bInitialised=false;
this.aoOpenRows=[];this.sDom="lfrtip";this.sPaginationType="two_button";this.iCookieDuration=60*60*2;
this.sAjaxSource=null;this.bAjaxDataGet=true;this.fnServerData=$.getJSON;this.iServerDraw=0;
this._iDisplayLength=10;this._iDisplayStart=0;this._iDisplayEnd=10;this._iRecordsTotal=0;
this._iRecordsDisplay=0;this.bJUI=false;this.oClasses=_oExt.oStdClasses;this.bFiltered=false;
this.bSorted=false}this.oApi={};this.fnDraw=function(bComplete){var oSettings=_fnSettingsFromNode(this[_oExt.iApiIndex]);
if(typeof bComplete!="undefined"&&bComplete===false){_fnCalculateEnd(oSettings);_fnDraw(oSettings)
}else{_fnReDraw(oSettings)}};this.fnFilter=function(sInput,iColumn,bEscapeRegex){var oSettings=_fnSettingsFromNode(this[_oExt.iApiIndex]);
if(typeof bEscapeRegex=="undefined"){bEscapeRegex=true}if(typeof iColumn=="undefined"||iColumn===null){_fnFilterComplete(oSettings,{sSearch:sInput,bEscapeRegex:bEscapeRegex},1)
}else{oSettings.aoPreSearchCols[iColumn].sSearch=sInput;oSettings.aoPreSearchCols[iColumn].bEscapeRegex=bEscapeRegex;
_fnFilterComplete(oSettings,oSettings.oPreviousSearch,1)}};this.fnSettings=function(nNode){return _fnSettingsFromNode(this[_oExt.iApiIndex])
};this.fnVersionCheck=function(sVersion){var fnZPad=function(Zpad,count){while(Zpad.length<count){Zpad+="0"
}return Zpad};var aThis=_oExt.sVersion.split(".");var aThat=sVersion.split(".");var sThis="",sThat="";
for(var i=0,iLen=aThat.length;i<iLen;i++){sThis+=fnZPad(aThis[i],3);sThat+=fnZPad(aThat[i],3)
}return parseInt(sThis,10)>=parseInt(sThat,10)};this.fnSort=function(aaSort){var oSettings=_fnSettingsFromNode(this[_oExt.iApiIndex]);
oSettings.aaSorting=aaSort;_fnSort(oSettings)};this.fnSortListener=function(nNode,iColumn,fnCallback){_fnSortAttachListener(_fnSettingsFromNode(this[_oExt.iApiIndex]),nNode,iColumn,fnCallback)
};this.fnAddData=function(mData,bRedraw){if(mData.length===0){return[]}var aiReturn=[];
var iTest;var oSettings=_fnSettingsFromNode(this[_oExt.iApiIndex]);if(typeof mData[0]=="object"){for(var i=0;
i<mData.length;i++){iTest=_fnAddData(oSettings,mData[i]);if(iTest==-1){return aiReturn
}aiReturn.push(iTest)}}else{iTest=_fnAddData(oSettings,mData);if(iTest==-1){return aiReturn
}aiReturn.push(iTest)}oSettings.aiDisplay=oSettings.aiDisplayMaster.slice();_fnBuildSearchArray(oSettings,1);
if(typeof bRedraw=="undefined"||bRedraw){_fnReDraw(oSettings)}return aiReturn};this.fnDeleteRow=function(mTarget,fnCallBack,bNullRow){var oSettings=_fnSettingsFromNode(this[_oExt.iApiIndex]);
var i,iAODataIndex;iAODataIndex=(typeof mTarget=="object")?_fnNodeToDataIndex(oSettings,mTarget):mTarget;
for(i=0;i<oSettings.aiDisplayMaster.length;i++){if(oSettings.aiDisplayMaster[i]==iAODataIndex){oSettings.aiDisplayMaster.splice(i,1);
break}}for(i=0;i<oSettings.aiDisplay.length;i++){if(oSettings.aiDisplay[i]==iAODataIndex){oSettings.aiDisplay.splice(i,1);
break}}_fnBuildSearchArray(oSettings,1);if(typeof fnCallBack=="function"){fnCallBack.call(this)
}if(oSettings._iDisplayStart>=oSettings.aiDisplay.length){oSettings._iDisplayStart-=oSettings._iDisplayLength;
if(oSettings._iDisplayStart<0){oSettings._iDisplayStart=0}}_fnCalculateEnd(oSettings);
_fnDraw(oSettings);var aData=oSettings.aoData[iAODataIndex]._aData.slice();if(typeof bNullRow!="undefined"&&bNullRow===true){oSettings.aoData[iAODataIndex]=null
}return aData};this.fnClearTable=function(bRedraw){var oSettings=_fnSettingsFromNode(this[_oExt.iApiIndex]);
_fnClearTable(oSettings);if(typeof bRedraw=="undefined"||bRedraw){_fnDraw(oSettings)
}};this.fnOpen=function(nTr,sHtml,sClass){var oSettings=_fnSettingsFromNode(this[_oExt.iApiIndex]);
this.fnClose(nTr);var nNewRow=document.createElement("tr");var nNewCell=document.createElement("td");
nNewRow.appendChild(nNewCell);nNewCell.className=sClass;nNewCell.colSpan=_fnVisbleColumns(oSettings);
nNewCell.innerHTML=sHtml;var nTrs=$("tbody tr",oSettings.nTable);if($.inArray(nTr,nTrs)!=-1){$(nNewRow).insertAfter(nTr)
}if(!oSettings.oFeatures.bServerSide){oSettings.aoOpenRows.push({nTr:nNewRow,nParent:nTr})
}return nNewRow};this.fnClose=function(nTr){var oSettings=_fnSettingsFromNode(this[_oExt.iApiIndex]);
for(var i=0;i<oSettings.aoOpenRows.length;i++){if(oSettings.aoOpenRows[i].nParent==nTr){var nTrParent=oSettings.aoOpenRows[i].nTr.parentNode;
if(nTrParent){nTrParent.removeChild(oSettings.aoOpenRows[i].nTr)}oSettings.aoOpenRows.splice(i,1);
return 0}}return 1};this.fnGetData=function(mRow){var oSettings=_fnSettingsFromNode(this[_oExt.iApiIndex]);
if(typeof mRow!="undefined"){var iRow=(typeof mRow=="object")?_fnNodeToDataIndex(oSettings,mRow):mRow;
return oSettings.aoData[iRow]._aData}return _fnGetDataMaster(oSettings)};this.fnGetNodes=function(iRow){var oSettings=_fnSettingsFromNode(this[_oExt.iApiIndex]);
if(typeof iRow!="undefined"){return oSettings.aoData[iRow].nTr}return _fnGetTrNodes(oSettings)
};this.fnGetPosition=function(nNode){var oSettings=_fnSettingsFromNode(this[_oExt.iApiIndex]);
var i;if(nNode.nodeName=="TR"){return _fnNodeToDataIndex(oSettings,nNode)}else{if(nNode.nodeName=="TD"){var iDataIndex=_fnNodeToDataIndex(oSettings,nNode.parentNode);
var iCorrector=0;for(var j=0;j<oSettings.aoColumns.length;j++){if(oSettings.aoColumns[j].bVisible){if(oSettings.aoData[iDataIndex].nTr.getElementsByTagName("td")[j-iCorrector]==nNode){return[iDataIndex,j-iCorrector,j]
}}else{iCorrector++}}}}return null};this.fnUpdate=function(mData,mRow,iColumn,bRedraw){var oSettings=_fnSettingsFromNode(this[_oExt.iApiIndex]);
var iVisibleColumn;var sDisplay;var iRow=(typeof mRow=="object")?_fnNodeToDataIndex(oSettings,mRow):mRow;
if(typeof mData!="object"){sDisplay=mData;oSettings.aoData[iRow]._aData[iColumn]=sDisplay;
if(oSettings.aoColumns[iColumn].fnRender!==null){sDisplay=oSettings.aoColumns[iColumn].fnRender({iDataRow:iRow,iDataColumn:iColumn,aData:oSettings.aoData[iRow]._aData,oSettings:oSettings});
if(oSettings.aoColumns[iColumn].bUseRendered){oSettings.aoData[iRow]._aData[iColumn]=sDisplay
}}iVisibleColumn=_fnColumnIndexToVisible(oSettings,iColumn);if(iVisibleColumn!==null){oSettings.aoData[iRow].nTr.getElementsByTagName("td")[iVisibleColumn].innerHTML=sDisplay
}}else{if(mData.length!=oSettings.aoColumns.length){alert("DataTables warning: An array passed to fnUpdate must have the same number of columns as the table in question - in this case "+oSettings.aoColumns.length);
return 1}for(var i=0;i<mData.length;i++){sDisplay=mData[i];oSettings.aoData[iRow]._aData[i]=sDisplay;
if(oSettings.aoColumns[i].fnRender!==null){sDisplay=oSettings.aoColumns[i].fnRender({iDataRow:iRow,iDataColumn:i,aData:oSettings.aoData[iRow]._aData,oSettings:oSettings});
if(oSettings.aoColumns[i].bUseRendered){oSettings.aoData[iRow]._aData[i]=sDisplay
}}iVisibleColumn=_fnColumnIndexToVisible(oSettings,i);if(iVisibleColumn!==null){oSettings.aoData[iRow].nTr.getElementsByTagName("td")[iVisibleColumn].innerHTML=sDisplay
}}}_fnBuildSearchArray(oSettings,1);if(typeof bRedraw!="undefined"&&bRedraw){_fnReDraw(oSettings)
}return 0};this.fnSetColumnVis=function(iCol,bShow){var oSettings=_fnSettingsFromNode(this[_oExt.iApiIndex]);
var i,iLen;var iColumns=oSettings.aoColumns.length;var nTd,anTds;if(oSettings.aoColumns[iCol].bVisible==bShow){return
}var nTrHead=$("thead:eq(0)>tr",oSettings.nTable)[0];var nTrFoot=$("tfoot:eq(0)>tr",oSettings.nTable)[0];
var anTheadTh=[];var anTfootTh=[];for(i=0;i<iColumns;i++){anTheadTh.push(oSettings.aoColumns[i].nTh);
anTfootTh.push(oSettings.aoColumns[i].nTf)}if(bShow){var iInsert=0;for(i=0;i<iCol;
i++){if(oSettings.aoColumns[i].bVisible){iInsert++}}if(iInsert>=_fnVisbleColumns(oSettings)){nTrHead.appendChild(anTheadTh[iCol]);
if(nTrFoot){nTrFoot.appendChild(anTfootTh[iCol])}for(i=0,iLen=oSettings.aoData.length;
i<iLen;i++){nTd=oSettings.aoData[i]._anHidden[iCol];oSettings.aoData[i].nTr.appendChild(nTd)
}}else{var iBefore;for(i=iCol;i<iColumns;i++){iBefore=_fnColumnIndexToVisible(oSettings,i);
if(iBefore!==null){break}}nTrHead.insertBefore(anTheadTh[iCol],nTrHead.getElementsByTagName("th")[iBefore]);
if(nTrFoot){nTrFoot.insertBefore(anTfootTh[iCol],nTrFoot.getElementsByTagName("th")[iBefore])
}anTds=_fnGetTdNodes(oSettings);for(i=0,iLen=oSettings.aoData.length;i<iLen;i++){nTd=oSettings.aoData[i]._anHidden[iCol];
oSettings.aoData[i].nTr.insertBefore(nTd,$(">td:eq("+iBefore+")",oSettings.aoData[i].nTr)[0])
}}oSettings.aoColumns[iCol].bVisible=true}else{nTrHead.removeChild(anTheadTh[iCol]);
if(nTrFoot){nTrFoot.removeChild(anTfootTh[iCol])}anTds=_fnGetTdNodes(oSettings);for(i=0,iLen=oSettings.aoData.length;
i<iLen;i++){nTd=anTds[(i*oSettings.aoColumns.length)+iCol];oSettings.aoData[i]._anHidden[iCol]=nTd;
nTd.parentNode.removeChild(nTd)}oSettings.aoColumns[iCol].bVisible=false}for(i=0,iLen=oSettings.aoOpenRows.length;
i<iLen;i++){oSettings.aoOpenRows[i].nTr.colSpan=_fnVisbleColumns(oSettings)}_fnSaveState(oSettings)
};this.fnPageChange=function(sAction,bRedraw){var oSettings=_fnSettingsFromNode(this[_oExt.iApiIndex]);
_fnPageChange(oSettings,sAction);_fnCalculateEnd(oSettings);if(typeof bRedraw=="undefined"||bRedraw){_fnDraw(oSettings)
}};function _fnExternApiFunc(sFunc){return function(){var aArgs=[_fnSettingsFromNode(this[_oExt.iApiIndex])].concat(Array.prototype.slice.call(arguments));
return _oExt.oApi[sFunc].apply(this,aArgs)}}for(var sFunc in _oExt.oApi){if(sFunc){this[sFunc]=_fnExternApiFunc(sFunc)
}}function _fnInitalise(oSettings){if(oSettings.bInitialised===false){setTimeout(function(){_fnInitalise(oSettings)
},200);return}_fnAddOptionsHtml(oSettings);_fnDrawHead(oSettings);if(oSettings.oFeatures.bSort){_fnSort(oSettings,false);
_fnSortingClasses(oSettings)}else{oSettings.aiDisplay=oSettings.aiDisplayMaster.slice();
_fnCalculateEnd(oSettings);_fnDraw(oSettings)}if(oSettings.sAjaxSource!==null&&!oSettings.oFeatures.bServerSide){_fnProcessingDisplay(oSettings,true);
oSettings.fnServerData(oSettings.sAjaxSource,null,function(json){for(var i=0;i<json.aaData.length;
i++){_fnAddData(oSettings,json.aaData[i])}oSettings.iInitDisplayStart=oSettings._iDisplayStart;
if(oSettings.oFeatures.bSort){_fnSort(oSettings)}else{oSettings.aiDisplay=oSettings.aiDisplayMaster.slice();
_fnCalculateEnd(oSettings);_fnDraw(oSettings)}_fnProcessingDisplay(oSettings,false);
if(typeof oSettings.fnInitComplete=="function"){oSettings.fnInitComplete(oSettings,json)
}});return}if(typeof oSettings.fnInitComplete=="function"){oSettings.fnInitComplete(oSettings)
}if(!oSettings.oFeatures.bServerSide){_fnProcessingDisplay(oSettings,false)}}function _fnLanguageProcess(oSettings,oLanguage,bInit){_fnMap(oSettings.oLanguage,oLanguage,"sProcessing");
_fnMap(oSettings.oLanguage,oLanguage,"sLengthMenu");_fnMap(oSettings.oLanguage,oLanguage,"sZeroRecords");
_fnMap(oSettings.oLanguage,oLanguage,"sInfo");_fnMap(oSettings.oLanguage,oLanguage,"sInfoEmpty");
_fnMap(oSettings.oLanguage,oLanguage,"sInfoFiltered");_fnMap(oSettings.oLanguage,oLanguage,"sInfoPostFix");
_fnMap(oSettings.oLanguage,oLanguage,"sSearch");if(typeof oLanguage.oPaginate!="undefined"){_fnMap(oSettings.oLanguage.oPaginate,oLanguage.oPaginate,"sFirst");
_fnMap(oSettings.oLanguage.oPaginate,oLanguage.oPaginate,"sPrevious");_fnMap(oSettings.oLanguage.oPaginate,oLanguage.oPaginate,"sNext");
_fnMap(oSettings.oLanguage.oPaginate,oLanguage.oPaginate,"sLast")}if(bInit){_fnInitalise(oSettings)
}}function _fnAddColumn(oSettings,oOptions,nTh){oSettings.aoColumns[oSettings.aoColumns.length++]={sType:null,_bAutoType:true,bVisible:true,bSearchable:true,bSortable:true,asSorting:["asc","desc"],sSortingClass:oSettings.oClasses.sSortable,sSortingClassJUI:oSettings.oClasses.sSortJUI,sTitle:nTh?nTh.innerHTML:"",sName:"",sWidth:null,sClass:null,fnRender:null,bUseRendered:true,iDataSort:oSettings.aoColumns.length-1,sSortDataType:"std",nTh:nTh?nTh:document.createElement("th"),nTf:null};
var iLength=oSettings.aoColumns.length-1;var oCol=oSettings.aoColumns[iLength];if(typeof oOptions!="undefined"&&oOptions!==null){if(typeof oOptions.sType!="undefined"){oCol.sType=oOptions.sType;
oCol._bAutoType=false}_fnMap(oCol,oOptions,"bVisible");_fnMap(oCol,oOptions,"bSearchable");
_fnMap(oCol,oOptions,"bSortable");_fnMap(oCol,oOptions,"sTitle");_fnMap(oCol,oOptions,"sName");
_fnMap(oCol,oOptions,"sWidth");_fnMap(oCol,oOptions,"sClass");_fnMap(oCol,oOptions,"fnRender");
_fnMap(oCol,oOptions,"bUseRendered");_fnMap(oCol,oOptions,"iDataSort");_fnMap(oCol,oOptions,"asSorting");
_fnMap(oCol,oOptions,"sSortDataType")}if(!oSettings.oFeatures.bSort){oCol.bSortable=false
}if(!oCol.bSortable||($.inArray("asc",oCol.asSorting)==-1&&$.inArray("desc",oCol.asSorting)==-1)){oCol.sSortingClass=oSettings.oClasses.sSortableNone;
oCol.sSortingClassJUI=""}else{if($.inArray("asc",oCol.asSorting)!=-1&&$.inArray("desc",oCol.asSorting)==-1){oCol.sSortingClass=oSettings.oClasses.sSortableAsc;
oCol.sSortingClassJUI=oSettings.oClasses.sSortJUIAscAllowed}else{if($.inArray("asc",oCol.asSorting)==-1&&$.inArray("desc",oCol.asSorting)!=-1){oCol.sSortingClass=oSettings.oClasses.sSortableDesc;
oCol.sSortingClassJUI=oSettings.oClasses.sSortJUIDescAllowed}}}if(typeof oSettings.aoPreSearchCols[iLength]=="undefined"||oSettings.aoPreSearchCols[iLength]===null){oSettings.aoPreSearchCols[iLength]={sSearch:"",bEscapeRegex:true}
}else{if(typeof oSettings.aoPreSearchCols[iLength].bEscapeRegex=="undefined"){oSettings.aoPreSearchCols[iLength].bEscapeRegex=true
}}}function _fnAddData(oSettings,aData){if(aData.length!=oSettings.aoColumns.length){alert("DataTables warning: Added data does not match known number of columns");
return -1}var iThisIndex=oSettings.aoData.length;oSettings.aoData.push({nTr:document.createElement("tr"),_iId:oSettings.iNextId++,_aData:aData.slice(),_anHidden:[],_sRowStripe:""});
var nTd,sThisType;for(var i=0;i<aData.length;i++){nTd=document.createElement("td");
if(typeof oSettings.aoColumns[i].fnRender=="function"){var sRendered=oSettings.aoColumns[i].fnRender({iDataRow:iThisIndex,iDataColumn:i,aData:aData,oSettings:oSettings});
nTd.innerHTML=sRendered;if(oSettings.aoColumns[i].bUseRendered){oSettings.aoData[iThisIndex]._aData[i]=sRendered
}}else{nTd.innerHTML=aData[i]}if(oSettings.aoColumns[i].sClass!==null){nTd.className=oSettings.aoColumns[i].sClass
}if(oSettings.aoColumns[i]._bAutoType&&oSettings.aoColumns[i].sType!="string"){sThisType=_fnDetectType(oSettings.aoData[iThisIndex]._aData[i]);
if(oSettings.aoColumns[i].sType===null){oSettings.aoColumns[i].sType=sThisType}else{if(oSettings.aoColumns[i].sType!=sThisType){oSettings.aoColumns[i].sType="string"
}}}if(oSettings.aoColumns[i].bVisible){oSettings.aoData[iThisIndex].nTr.appendChild(nTd)
}else{oSettings.aoData[iThisIndex]._anHidden[i]=nTd}}oSettings.aiDisplayMaster.push(iThisIndex);
return iThisIndex}function _fnGatherData(oSettings){var iLoop,i,iLen,j,jLen,jInner,nTds,nTrs,nTd,aLocalData,iThisIndex,iRow,iRows,iColumn,iColumns;
if(oSettings.sAjaxSource===null){nTrs=oSettings.nTable.getElementsByTagName("tbody")[0].childNodes;
for(i=0,iLen=nTrs.length;i<iLen;i++){if(nTrs[i].nodeName=="TR"){iThisIndex=oSettings.aoData.length;
oSettings.aoData.push({nTr:nTrs[i],_iId:oSettings.iNextId++,_aData:[],_anHidden:[],_sRowStripe:""});
oSettings.aiDisplayMaster.push(iThisIndex);aLocalData=oSettings.aoData[iThisIndex]._aData;
nTds=nTrs[i].childNodes;jInner=0;for(j=0,jLen=nTds.length;j<jLen;j++){if(nTds[j].nodeName=="TD"){aLocalData[jInner]=nTds[j].innerHTML;
jInner++}}}}}nTrs=_fnGetTrNodes(oSettings);nTds=[];for(i=0,iLen=nTrs.length;i<iLen;
i++){for(j=0,jLen=nTrs[i].childNodes.length;j<jLen;j++){nTd=nTrs[i].childNodes[j];
if(nTd.nodeName=="TD"){nTds.push(nTd)}}}if(nTds.length!=nTrs.length*oSettings.aoColumns.length){alert("DataTables warning: Unexpected number of TD elements. Expected "+(nTrs.length*oSettings.aoColumns.length)+" and got "+nTds.length+". DataTables does not support rowspan / colspan in the table body, and there must be one cell for each row/column combination.")
}for(iColumn=0,iColumns=oSettings.aoColumns.length;iColumn<iColumns;iColumn++){if(oSettings.aoColumns[iColumn].sTitle===null){oSettings.aoColumns[iColumn].sTitle=oSettings.aoColumns[iColumn].nTh.innerHTML
}var bAutoType=oSettings.aoColumns[iColumn]._bAutoType,bRender=typeof oSettings.aoColumns[iColumn].fnRender=="function",bClass=oSettings.aoColumns[iColumn].sClass!==null,bVisible=oSettings.aoColumns[iColumn].bVisible,nCell,sThisType,sRendered;
if(bAutoType||bRender||bClass||!bVisible){for(iRow=0,iRows=oSettings.aoData.length;
iRow<iRows;iRow++){nCell=nTds[(iRow*iColumns)+iColumn];if(bAutoType){if(oSettings.aoColumns[iColumn].sType!="string"){sThisType=_fnDetectType(oSettings.aoData[iRow]._aData[iColumn]);
if(oSettings.aoColumns[iColumn].sType===null){oSettings.aoColumns[iColumn].sType=sThisType
}else{if(oSettings.aoColumns[iColumn].sType!=sThisType){oSettings.aoColumns[iColumn].sType="string"
}}}}if(bRender){sRendered=oSettings.aoColumns[iColumn].fnRender({iDataRow:iRow,iDataColumn:iColumn,aData:oSettings.aoData[iRow]._aData,oSettings:oSettings});
nCell.innerHTML=sRendered;if(oSettings.aoColumns[iColumn].bUseRendered){oSettings.aoData[iRow]._aData[iColumn]=sRendered
}}if(bClass){nCell.className+=" "+oSettings.aoColumns[iColumn].sClass}if(!bVisible){oSettings.aoData[iRow]._anHidden[iColumn]=nCell;
nCell.parentNode.removeChild(nCell)}}}}}function _fnDrawHead(oSettings){var i,nTh,iLen;
var iThs=oSettings.nTable.getElementsByTagName("thead")[0].getElementsByTagName("th").length;
var iCorrector=0;if(iThs!==0){for(i=0,iLen=oSettings.aoColumns.length;i<iLen;i++){nTh=oSettings.aoColumns[i].nTh;
if(oSettings.aoColumns[i].bVisible){if(oSettings.aoColumns[i].sWidth!==null){nTh.style.width=oSettings.aoColumns[i].sWidth
}if(oSettings.aoColumns[i].sTitle!=nTh.innerHTML){nTh.innerHTML=oSettings.aoColumns[i].sTitle
}}else{nTh.parentNode.removeChild(nTh);iCorrector++}}}else{var nTr=document.createElement("tr");
for(i=0,iLen=oSettings.aoColumns.length;i<iLen;i++){nTh=oSettings.aoColumns[i].nTh;
nTh.innerHTML=oSettings.aoColumns[i].sTitle;if(oSettings.aoColumns[i].bVisible){if(oSettings.aoColumns[i].sClass!==null){nTh.className=oSettings.aoColumns[i].sClass
}if(oSettings.aoColumns[i].sWidth!==null){nTh.style.width=oSettings.aoColumns[i].sWidth
}nTr.appendChild(nTh)}}$("thead:eq(0)",oSettings.nTable).html("")[0].appendChild(nTr)
}if(oSettings.bJUI){for(i=0,iLen=oSettings.aoColumns.length;i<iLen;i++){oSettings.aoColumns[i].nTh.insertBefore(document.createElement("span"),oSettings.aoColumns[i].nTh.firstChild)
}}if(oSettings.oFeatures.bSort){for(i=0;i<oSettings.aoColumns.length;i++){if(oSettings.aoColumns[i].bSortable!==false){_fnSortAttachListener(oSettings,oSettings.aoColumns[i].nTh,i)
}else{$(oSettings.aoColumns[i].nTh).addClass(oSettings.oClasses.sSortableNone)}}$("thead:eq(0) th",oSettings.nTable).mousedown(function(e){if(e.shiftKey){this.onselectstart=function(){return false
};return false}})}var nTfoot=oSettings.nTable.getElementsByTagName("tfoot");if(nTfoot.length!==0){iCorrector=0;
var nTfs=nTfoot[0].getElementsByTagName("th");for(i=0,iLen=nTfs.length;i<iLen;i++){oSettings.aoColumns[i].nTf=nTfs[i-iCorrector];
if(!oSettings.aoColumns[i].bVisible){nTfs[i-iCorrector].parentNode.removeChild(nTfs[i-iCorrector]);
iCorrector++}}}}function _fnDraw(oSettings){var i,iLen;var anRows=[];var iRowCount=0;
var bRowError=false;var iStrips=oSettings.asStripClasses.length;var iOpenRows=oSettings.aoOpenRows.length;
if(oSettings.oFeatures.bServerSide&&!_fnAjaxUpdate(oSettings)){return}if(typeof oSettings.iInitDisplayStart!="undefined"&&oSettings.iInitDisplayStart!=-1){oSettings._iDisplayStart=(oSettings.iInitDisplayStart>=oSettings.fnRecordsDisplay())?0:oSettings.iInitDisplayStart;
oSettings.iInitDisplayStart=-1;_fnCalculateEnd(oSettings)}if(oSettings.aiDisplay.length!==0){var iStart=oSettings._iDisplayStart;
var iEnd=oSettings._iDisplayEnd;if(oSettings.oFeatures.bServerSide){iStart=0;iEnd=oSettings.aoData.length
}for(var j=iStart;j<iEnd;j++){var aoData=oSettings.aoData[oSettings.aiDisplay[j]];
var nRow=aoData.nTr;if(iStrips!==0){var sStrip=oSettings.asStripClasses[iRowCount%iStrips];
if(aoData._sRowStripe!=sStrip){$(nRow).removeClass(aoData._sRowStripe).addClass(sStrip);
aoData._sRowStripe=sStrip}}if(typeof oSettings.fnRowCallback=="function"){nRow=oSettings.fnRowCallback(nRow,oSettings.aoData[oSettings.aiDisplay[j]]._aData,iRowCount,j);
if(!nRow&&!bRowError){alert("DataTables warning: A node was not returned by fnRowCallback");
bRowError=true}}anRows.push(nRow);iRowCount++;if(iOpenRows!==0){for(var k=0;k<iOpenRows;
k++){if(nRow==oSettings.aoOpenRows[k].nParent){anRows.push(oSettings.aoOpenRows[k].nTr)
}}}}}else{anRows[0]=document.createElement("tr");if(typeof oSettings.asStripClasses[0]!="undefined"){anRows[0].className=oSettings.asStripClasses[0]
}var nTd=document.createElement("td");nTd.setAttribute("valign","top");nTd.colSpan=oSettings.aoColumns.length;
nTd.className=oSettings.oClasses.sRowEmpty;nTd.innerHTML=oSettings.oLanguage.sZeroRecords;
anRows[iRowCount].appendChild(nTd)}if(typeof oSettings.fnHeaderCallback=="function"){oSettings.fnHeaderCallback($("thead:eq(0)>tr",oSettings.nTable)[0],_fnGetDataMaster(oSettings),oSettings._iDisplayStart,oSettings.fnDisplayEnd(),oSettings.aiDisplay)
}if(typeof oSettings.fnFooterCallback=="function"){oSettings.fnFooterCallback($("tfoot:eq(0)>tr",oSettings.nTable)[0],_fnGetDataMaster(oSettings),oSettings._iDisplayStart,oSettings.fnDisplayEnd(),oSettings.aiDisplay)
}var nBody=oSettings.nTable.getElementsByTagName("tbody");if(nBody[0]){var nTrs=nBody[0].childNodes;
for(i=nTrs.length-1;i>=0;i--){nTrs[i].parentNode.removeChild(nTrs[i])}for(i=0,iLen=anRows.length;
i<iLen;i++){nBody[0].appendChild(anRows[i])}}for(i=0,iLen=oSettings.aoDrawCallback.length;
i<iLen;i++){oSettings.aoDrawCallback[i].fn(oSettings)}oSettings.bSorted=false;oSettings.bFiltered=false;
if(typeof oSettings._bInitComplete=="undefined"){oSettings._bInitComplete=true;if(oSettings.oFeatures.bAutoWidth&&oSettings.nTable.offsetWidth!==0){oSettings.nTable.style.width=oSettings.nTable.offsetWidth+"px"
}}}function _fnReDraw(oSettings){if(oSettings.oFeatures.bSort){_fnSort(oSettings,oSettings.oPreviousSearch)
}else{if(oSettings.oFeatures.bFilter){_fnFilterComplete(oSettings,oSettings.oPreviousSearch)
}else{_fnCalculateEnd(oSettings);_fnDraw(oSettings)}}}function _fnAjaxUpdate(oSettings){if(oSettings.bAjaxDataGet){_fnProcessingDisplay(oSettings,true);
var iColumns=oSettings.aoColumns.length;var aoData=[];var i;oSettings.iServerDraw++;
aoData.push({name:"sEcho",value:oSettings.iServerDraw});aoData.push({name:"iColumns",value:iColumns});
aoData.push({name:"sColumns",value:_fnColumnOrdering(oSettings)});aoData.push({name:"iDisplayStart",value:oSettings._iDisplayStart});
aoData.push({name:"iDisplayLength",value:oSettings.oFeatures.bPaginate!==false?oSettings._iDisplayLength:-1});
if(oSettings.oFeatures.bFilter!==false){aoData.push({name:"sSearch",value:oSettings.oPreviousSearch.sSearch});
aoData.push({name:"bEscapeRegex",value:oSettings.oPreviousSearch.bEscapeRegex});for(i=0;
i<iColumns;i++){aoData.push({name:"sSearch_"+i,value:oSettings.aoPreSearchCols[i].sSearch});
aoData.push({name:"bEscapeRegex_"+i,value:oSettings.aoPreSearchCols[i].bEscapeRegex});
aoData.push({name:"bSearchable_"+i,value:oSettings.aoColumns[i].bSearchable})}}if(oSettings.oFeatures.bSort!==false){var iFixed=oSettings.aaSortingFixed!==null?oSettings.aaSortingFixed.length:0;
var iUser=oSettings.aaSorting.length;aoData.push({name:"iSortingCols",value:iFixed+iUser});
for(i=0;i<iFixed;i++){aoData.push({name:"iSortCol_"+i,value:oSettings.aaSortingFixed[i][0]});
aoData.push({name:"sSortDir_"+i,value:oSettings.aaSortingFixed[i][1]})}for(i=0;i<iUser;
i++){aoData.push({name:"iSortCol_"+(i+iFixed),value:oSettings.aaSorting[i][0]});aoData.push({name:"sSortDir_"+(i+iFixed),value:oSettings.aaSorting[i][1]})
}for(i=0;i<iColumns;i++){aoData.push({name:"bSortable_"+i,value:oSettings.aoColumns[i].bSortable})
}}oSettings.fnServerData(oSettings.sAjaxSource,aoData,function(json){_fnAjaxUpdateDraw(oSettings,json)
});return false}else{return true}}function _fnAjaxUpdateDraw(oSettings,json){if(typeof json.sEcho!="undefined"){if(json.sEcho*1<oSettings.iServerDraw){return
}else{oSettings.iServerDraw=json.sEcho*1}}_fnClearTable(oSettings);oSettings._iRecordsTotal=json.iTotalRecords;
oSettings._iRecordsDisplay=json.iTotalDisplayRecords;var sOrdering=_fnColumnOrdering(oSettings);
var bReOrder=(typeof json.sColumns!="undefined"&&sOrdering!==""&&json.sColumns!=sOrdering);
if(bReOrder){var aiIndex=_fnReOrderIndex(oSettings,json.sColumns)}for(var i=0,iLen=json.aaData.length;
i<iLen;i++){if(bReOrder){var aData=[];for(var j=0,jLen=oSettings.aoColumns.length;
j<jLen;j++){aData.push(json.aaData[i][aiIndex[j]])}_fnAddData(oSettings,aData)}else{_fnAddData(oSettings,json.aaData[i])
}}oSettings.aiDisplay=oSettings.aiDisplayMaster.slice();oSettings.bAjaxDataGet=false;
_fnDraw(oSettings);oSettings.bAjaxDataGet=true;_fnProcessingDisplay(oSettings,false)
}function _fnAddOptionsHtml(oSettings){var nHolding=document.createElement("div");
oSettings.nTable.parentNode.insertBefore(nHolding,oSettings.nTable);var nWrapper=document.createElement("div");
nWrapper.className=oSettings.oClasses.sWrapper;if(oSettings.sTableId!==""){nWrapper.setAttribute("id",oSettings.sTableId+"_wrapper")
}var nInsertNode=nWrapper;var sDom=oSettings.sDom.replace("H","fg-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix");
sDom=sDom.replace("F","fg-toolbar ui-widget-header ui-corner-bl ui-corner-br ui-helper-clearfix");
var aDom=sDom.split("");var nTmp,iPushFeature,cOption,nNewNode,cNext,sClass,j;for(var i=0;
i<aDom.length;i++){iPushFeature=0;cOption=aDom[i];if(cOption=="<"){nNewNode=document.createElement("div");
cNext=aDom[i+1];if(cNext=="'"||cNext=='"'){sClass="";j=2;while(aDom[i+j]!=cNext){sClass+=aDom[i+j];
j++}nNewNode.className=sClass;i+=j}nInsertNode.appendChild(nNewNode);nInsertNode=nNewNode
}else{if(cOption==">"){nInsertNode=nInsertNode.parentNode}else{if(cOption=="l"&&oSettings.oFeatures.bPaginate&&oSettings.oFeatures.bLengthChange){nTmp=_fnFeatureHtmlLength(oSettings);
iPushFeature=1}else{if(cOption=="f"&&oSettings.oFeatures.bFilter){nTmp=_fnFeatureHtmlFilter(oSettings);
iPushFeature=1}else{if(cOption=="r"&&oSettings.oFeatures.bProcessing){nTmp=_fnFeatureHtmlProcessing(oSettings);
iPushFeature=1}else{if(cOption=="t"){nTmp=oSettings.nTable;iPushFeature=1}else{if(cOption=="i"&&oSettings.oFeatures.bInfo){nTmp=_fnFeatureHtmlInfo(oSettings);
iPushFeature=1}else{if(cOption=="p"&&oSettings.oFeatures.bPaginate){nTmp=_fnFeatureHtmlPaginate(oSettings);
iPushFeature=1}else{if(_oExt.aoFeatures.length!==0){var aoFeatures=_oExt.aoFeatures;
for(var k=0,kLen=aoFeatures.length;k<kLen;k++){if(cOption==aoFeatures[k].cFeature){nTmp=aoFeatures[k].fnInit(oSettings);
if(nTmp){iPushFeature=1}break}}}}}}}}}}}if(iPushFeature==1){if(typeof oSettings.aanFeatures[cOption]!="object"){oSettings.aanFeatures[cOption]=[]
}oSettings.aanFeatures[cOption].push(nTmp);nInsertNode.appendChild(nTmp)}}nHolding.parentNode.replaceChild(nWrapper,nHolding)
}function _fnFeatureHtmlFilter(oSettings){var nFilter=document.createElement("div");
if(oSettings.sTableId!==""&&typeof oSettings.aanFeatures.f=="undefined"){nFilter.setAttribute("id",oSettings.sTableId+"_filter")
}nFilter.className=oSettings.oClasses.sFilter;var sSpace=oSettings.oLanguage.sSearch===""?"":" ";
nFilter.innerHTML=oSettings.oLanguage.sSearch+sSpace+'<input type="text" />';var jqFilter=$("input",nFilter);
jqFilter.val(oSettings.oPreviousSearch.sSearch.replace('"',"&quot;"));jqFilter.keyup(function(e){var n=oSettings.aanFeatures.f;
for(var i=0,iLen=n.length;i<iLen;i++){if(n[i]!=this.parentNode){$("input",n[i]).val(this.value)
}}_fnFilterComplete(oSettings,{sSearch:this.value,bEscapeRegex:oSettings.oPreviousSearch.bEscapeRegex})
});jqFilter.keypress(function(e){if(e.keyCode==13){return false}});return nFilter
}function _fnFilterComplete(oSettings,oInput,iForce){_fnFilter(oSettings,oInput.sSearch,iForce,oInput.bEscapeRegex);
for(var i=0;i<oSettings.aoPreSearchCols.length;i++){_fnFilterColumn(oSettings,oSettings.aoPreSearchCols[i].sSearch,i,oSettings.aoPreSearchCols[i].bEscapeRegex)
}if(_oExt.afnFiltering.length!==0){_fnFilterCustom(oSettings)}oSettings.bFiltered=true;
oSettings._iDisplayStart=0;_fnCalculateEnd(oSettings);_fnDraw(oSettings);_fnBuildSearchArray(oSettings,0)
}function _fnFilterCustom(oSettings){var afnFilters=_oExt.afnFiltering;for(var i=0,iLen=afnFilters.length;
i<iLen;i++){var iCorrector=0;for(var j=0,jLen=oSettings.aiDisplay.length;j<jLen;j++){var iDisIndex=oSettings.aiDisplay[j-iCorrector];
if(!afnFilters[i](oSettings,oSettings.aoData[iDisIndex]._aData,iDisIndex)){oSettings.aiDisplay.splice(j-iCorrector,1);
iCorrector++}}}}function _fnFilterColumn(oSettings,sInput,iColumn,bEscapeRegex){if(sInput===""){return
}var iIndexCorrector=0;var sRegexMatch=bEscapeRegex?_fnEscapeRegex(sInput):sInput;
var rpSearch=new RegExp(sRegexMatch,"i");for(var i=oSettings.aiDisplay.length-1;i>=0;
i--){var sData=_fnDataToSearch(oSettings.aoData[oSettings.aiDisplay[i]]._aData[iColumn],oSettings.aoColumns[iColumn].sType);
if(!rpSearch.test(sData)){oSettings.aiDisplay.splice(i,1);iIndexCorrector++}}}function _fnFilter(oSettings,sInput,iForce,bEscapeRegex){var i;
if(typeof iForce=="undefined"||iForce===null){iForce=0}if(_oExt.afnFiltering.length!==0){iForce=1
}var asSearch=bEscapeRegex?_fnEscapeRegex(sInput).split(" "):sInput.split(" ");var sRegExpString="^(?=.*?"+asSearch.join(")(?=.*?")+").*$";
var rpSearch=new RegExp(sRegExpString,"i");if(sInput.length<=0){oSettings.aiDisplay.splice(0,oSettings.aiDisplay.length);
oSettings.aiDisplay=oSettings.aiDisplayMaster.slice()}else{if(oSettings.aiDisplay.length==oSettings.aiDisplayMaster.length||oSettings.oPreviousSearch.sSearch.length>sInput.length||iForce==1||sInput.indexOf(oSettings.oPreviousSearch.sSearch)!==0){oSettings.aiDisplay.splice(0,oSettings.aiDisplay.length);
_fnBuildSearchArray(oSettings,1);for(i=0;i<oSettings.aiDisplayMaster.length;i++){if(rpSearch.test(oSettings.asDataSearch[i])){oSettings.aiDisplay.push(oSettings.aiDisplayMaster[i])
}}}else{var iIndexCorrector=0;for(i=0;i<oSettings.asDataSearch.length;i++){if(!rpSearch.test(oSettings.asDataSearch[i])){oSettings.aiDisplay.splice(i-iIndexCorrector,1);
iIndexCorrector++}}}}oSettings.oPreviousSearch.sSearch=sInput;oSettings.oPreviousSearch.bEscapeRegex=bEscapeRegex
}function _fnBuildSearchArray(oSettings,iMaster){oSettings.asDataSearch.splice(0,oSettings.asDataSearch.length);
var aArray=(typeof iMaster!="undefined"&&iMaster==1)?oSettings.aiDisplayMaster:oSettings.aiDisplay;
for(var i=0,iLen=aArray.length;i<iLen;i++){oSettings.asDataSearch[i]="";for(var j=0,jLen=oSettings.aoColumns.length;
j<jLen;j++){if(oSettings.aoColumns[j].bSearchable){var sData=oSettings.aoData[aArray[i]]._aData[j];
oSettings.asDataSearch[i]+=_fnDataToSearch(sData,oSettings.aoColumns[j].sType)+" "
}}}}function _fnDataToSearch(sData,sType){if(typeof _oExt.ofnSearch[sType]=="function"){return _oExt.ofnSearch[sType](sData)
}else{if(sType=="html"){return sData.replace(/\n/g," ").replace(/<.*?>/g,"")}else{if(typeof sData=="string"){return sData.replace(/\n/g," ")
}}}return sData}function _fnSort(oSettings,bApplyClasses){var aaSort=[];var oSort=_oExt.oSort;
var aoData=oSettings.aoData;var iDataSort;var iDataType;var i,j,jLen;if(!oSettings.oFeatures.bServerSide&&(oSettings.aaSorting.length!==0||oSettings.aaSortingFixed!==null)){if(oSettings.aaSortingFixed!==null){aaSort=oSettings.aaSortingFixed.concat(oSettings.aaSorting)
}else{aaSort=oSettings.aaSorting.slice()}for(i=0;i<aaSort.length;i++){var iColumn=aaSort[i][0];
var sDataType=oSettings.aoColumns[iColumn].sSortDataType;if(typeof _oExt.afnSortData[sDataType]!="undefined"){var iCorrector=0;
var aData=_oExt.afnSortData[sDataType](oSettings,iColumn);for(j=0,jLen=aoData.length;
j<jLen;j++){if(aoData[j]!==null){aoData[j]._aData[iColumn]=aData[iCorrector];iCorrector++
}}}}if(!window.runtime){var fnLocalSorting;var sDynamicSort="fnLocalSorting = function(a,b){var iTest;";
for(i=0;i<aaSort.length-1;i++){iDataSort=oSettings.aoColumns[aaSort[i][0]].iDataSort;
iDataType=oSettings.aoColumns[iDataSort].sType;sDynamicSort+="iTest = oSort['"+iDataType+"-"+aaSort[i][1]+"']( aoData[a]._aData["+iDataSort+"], aoData[b]._aData["+iDataSort+"] ); if ( iTest === 0 )"
}iDataSort=oSettings.aoColumns[aaSort[aaSort.length-1][0]].iDataSort;iDataType=oSettings.aoColumns[iDataSort].sType;
sDynamicSort+="iTest = oSort['"+iDataType+"-"+aaSort[aaSort.length-1][1]+"']( aoData[a]._aData["+iDataSort+"], aoData[b]._aData["+iDataSort+"] );if (iTest===0) return oSort['numeric-"+aaSort[aaSort.length-1][1]+"'](a, b); return iTest;}";
eval(sDynamicSort);oSettings.aiDisplayMaster.sort(fnLocalSorting)}else{var aAirSort=[];
var iLen=aaSort.length;for(i=0;i<iLen;i++){iDataSort=oSettings.aoColumns[aaSort[i][0]].iDataSort;
aAirSort.push([iDataSort,oSettings.aoColumns[iDataSort].sType+"-"+aaSort[i][1]])}oSettings.aiDisplayMaster.sort(function(a,b){var iTest;
for(var i=0;i<iLen;i++){iTest=oSort[aAirSort[i][1]](aoData[a]._aData[aAirSort[i][0]],aoData[b]._aData[aAirSort[i][0]]);
if(iTest!==0){return iTest}}return 0})}}if(typeof bApplyClasses=="undefined"||bApplyClasses){_fnSortingClasses(oSettings)
}oSettings.bSorted=true;if(oSettings.oFeatures.bFilter){_fnFilterComplete(oSettings,oSettings.oPreviousSearch,1)
}else{oSettings.aiDisplay=oSettings.aiDisplayMaster.slice();oSettings._iDisplayStart=0;
_fnCalculateEnd(oSettings);_fnDraw(oSettings)}}function _fnSortAttachListener(oSettings,nNode,iDataIndex,fnCallback){$(nNode).click(function(e){if(oSettings.aoColumns[iDataIndex].bSortable===false){return
}var fnInnerSorting=function(){var iColumn,iNextSort;if(e.shiftKey){var bFound=false;
for(var i=0;i<oSettings.aaSorting.length;i++){if(oSettings.aaSorting[i][0]==iDataIndex){bFound=true;
iColumn=oSettings.aaSorting[i][0];iNextSort=oSettings.aaSorting[i][2]+1;if(typeof oSettings.aoColumns[iColumn].asSorting[iNextSort]=="undefined"){oSettings.aaSorting.splice(i,1)
}else{oSettings.aaSorting[i][1]=oSettings.aoColumns[iColumn].asSorting[iNextSort];
oSettings.aaSorting[i][2]=iNextSort}break}}if(bFound===false){oSettings.aaSorting.push([iDataIndex,oSettings.aoColumns[iDataIndex].asSorting[0],0])
}}else{if(oSettings.aaSorting.length==1&&oSettings.aaSorting[0][0]==iDataIndex){iColumn=oSettings.aaSorting[0][0];
iNextSort=oSettings.aaSorting[0][2]+1;if(typeof oSettings.aoColumns[iColumn].asSorting[iNextSort]=="undefined"){iNextSort=0
}oSettings.aaSorting[0][1]=oSettings.aoColumns[iColumn].asSorting[iNextSort];oSettings.aaSorting[0][2]=iNextSort
}else{oSettings.aaSorting.splice(0,oSettings.aaSorting.length);oSettings.aaSorting.push([iDataIndex,oSettings.aoColumns[iDataIndex].asSorting[0],0])
}}_fnSort(oSettings)};if(!oSettings.oFeatures.bProcessing){fnInnerSorting()}else{_fnProcessingDisplay(oSettings,true);
setTimeout(function(){fnInnerSorting();if(!oSettings.oFeatures.bServerSide){_fnProcessingDisplay(oSettings,false)
}},0)}if(typeof fnCallback=="function"){fnCallback(oSettings)}})}function _fnSortingClasses(oSettings){var i,iLen,j,jLen,iFound;
var aaSort,sClass;var iColumns=oSettings.aoColumns.length;var oClasses=oSettings.oClasses;
for(i=0;i<iColumns;i++){if(oSettings.aoColumns[i].bSortable){$(oSettings.aoColumns[i].nTh).removeClass(oClasses.sSortAsc+" "+oClasses.sSortDesc+" "+oSettings.aoColumns[i].sSortingClass)
}}if(oSettings.aaSortingFixed!==null){aaSort=oSettings.aaSortingFixed.concat(oSettings.aaSorting)
}else{aaSort=oSettings.aaSorting.slice()}for(i=0;i<oSettings.aoColumns.length;i++){if(oSettings.aoColumns[i].bSortable){sClass=oSettings.aoColumns[i].sSortingClass;
iFound=-1;for(j=0;j<aaSort.length;j++){if(aaSort[j][0]==i){sClass=(aaSort[j][1]=="asc")?oClasses.sSortAsc:oClasses.sSortDesc;
iFound=j;break}}$(oSettings.aoColumns[i].nTh).addClass(sClass);if(oSettings.bJUI){var jqSpan=$("span",oSettings.aoColumns[i].nTh);
jqSpan.removeClass(oClasses.sSortJUIAsc+" "+oClasses.sSortJUIDesc+" "+oClasses.sSortJUI+" "+oClasses.sSortJUIAscAllowed+" "+oClasses.sSortJUIDescAllowed);
var sSpanClass;if(iFound==-1){sSpanClass=oSettings.aoColumns[i].sSortingClassJUI}else{if(aaSort[iFound][1]=="asc"){sSpanClass=oClasses.sSortJUIAsc
}else{sSpanClass=oClasses.sSortJUIDesc}}jqSpan.addClass(sSpanClass)}}else{$(oSettings.aoColumns[i].nTh).addClass(oSettings.aoColumns[i].sSortingClass)
}}sClass=oClasses.sSortColumn;if(oSettings.oFeatures.bSort&&oSettings.oFeatures.bSortClasses){var nTds=_fnGetTdNodes(oSettings);
if(nTds.length>=iColumns){for(i=0;i<iColumns;i++){if(nTds[i].className.indexOf(sClass+"1")!=-1){for(j=0,jLen=(nTds.length/iColumns);
j<jLen;j++){nTds[(iColumns*j)+i].className=nTds[(iColumns*j)+i].className.replace(" "+sClass+"1","")
}}else{if(nTds[i].className.indexOf(sClass+"2")!=-1){for(j=0,jLen=(nTds.length/iColumns);
j<jLen;j++){nTds[(iColumns*j)+i].className=nTds[(iColumns*j)+i].className.replace(" "+sClass+"2","")
}}else{if(nTds[i].className.indexOf(sClass+"3")!=-1){for(j=0,jLen=(nTds.length/iColumns);
j<jLen;j++){nTds[(iColumns*j)+i].className=nTds[(iColumns*j)+i].className.replace(" "+sClass+"3","")
}}}}}}var iClass=1,iTargetCol;for(i=0;i<aaSort.length;i++){iTargetCol=parseInt(aaSort[i][0],10);
for(j=0,jLen=(nTds.length/iColumns);j<jLen;j++){nTds[(iColumns*j)+iTargetCol].className+=" "+sClass+iClass
}if(iClass<3){iClass++}}}}function _fnFeatureHtmlPaginate(oSettings){var nPaginate=document.createElement("div");
nPaginate.className=oSettings.oClasses.sPaging+oSettings.sPaginationType;_oExt.oPagination[oSettings.sPaginationType].fnInit(oSettings,nPaginate,function(oSettings){_fnCalculateEnd(oSettings);
_fnDraw(oSettings)});if(typeof oSettings.aanFeatures.p=="undefined"){oSettings.aoDrawCallback.push({fn:function(oSettings){_oExt.oPagination[oSettings.sPaginationType].fnUpdate(oSettings,function(oSettings){_fnCalculateEnd(oSettings);
_fnDraw(oSettings)})},sName:"pagination"})}return nPaginate}function _fnPageChange(oSettings,sAction){var iOldStart=oSettings._iDisplayStart;
if(sAction=="first"){oSettings._iDisplayStart=0}else{if(sAction=="previous"){oSettings._iDisplayStart=oSettings._iDisplayLength>=0?oSettings._iDisplayStart-oSettings._iDisplayLength:0;
if(oSettings._iDisplayStart<0){oSettings._iDisplayStart=0}}else{if(sAction=="next"){if(oSettings._iDisplayLength>=0){if(oSettings._iDisplayStart+oSettings._iDisplayLength<oSettings.fnRecordsDisplay()){oSettings._iDisplayStart+=oSettings._iDisplayLength
}}else{oSettings._iDisplayStart=0}}else{if(sAction=="last"){if(oSettings._iDisplayLength>=0){var iPages=parseInt((oSettings.fnRecordsDisplay()-1)/oSettings._iDisplayLength,10)+1;
oSettings._iDisplayStart=(iPages-1)*oSettings._iDisplayLength}else{oSettings._iDisplayStart=0
}}else{alert("DataTables warning: unknown paging action: "+sAction)}}}}return iOldStart!=oSettings._iDisplayStart
}function _fnFeatureHtmlInfo(oSettings){var nInfo=document.createElement("div");nInfo.className=oSettings.oClasses.sInfo;
if(typeof oSettings.aanFeatures.i=="undefined"){oSettings.aoDrawCallback.push({fn:_fnUpdateInfo,sName:"information"});
if(oSettings.sTableId!==""){nInfo.setAttribute("id",oSettings.sTableId+"_info")}}return nInfo
}function _fnUpdateInfo(oSettings){if(!oSettings.oFeatures.bInfo||oSettings.aanFeatures.i.length===0){return
}var nFirst=oSettings.aanFeatures.i[0];if(oSettings.fnRecordsDisplay()===0&&oSettings.fnRecordsDisplay()==oSettings.fnRecordsTotal()){nFirst.innerHTML=oSettings.oLanguage.sInfoEmpty+oSettings.oLanguage.sInfoPostFix
}else{if(oSettings.fnRecordsDisplay()===0){nFirst.innerHTML=oSettings.oLanguage.sInfoEmpty+" "+oSettings.oLanguage.sInfoFiltered.replace("_MAX_",oSettings.fnRecordsTotal())+oSettings.oLanguage.sInfoPostFix
}else{if(oSettings.fnRecordsDisplay()==oSettings.fnRecordsTotal()){nFirst.innerHTML=oSettings.oLanguage.sInfo.replace("_START_",oSettings._iDisplayStart+1).replace("_END_",oSettings.fnDisplayEnd()).replace("_TOTAL_",oSettings.fnRecordsDisplay())+oSettings.oLanguage.sInfoPostFix
}else{nFirst.innerHTML=oSettings.oLanguage.sInfo.replace("_START_",oSettings._iDisplayStart+1).replace("_END_",oSettings.fnDisplayEnd()).replace("_TOTAL_",oSettings.fnRecordsDisplay())+" "+oSettings.oLanguage.sInfoFiltered.replace("_MAX_",oSettings.fnRecordsTotal())+oSettings.oLanguage.sInfoPostFix
}}}var n=oSettings.aanFeatures.i;if(n.length>1){var sInfo=nFirst.innerHTML;for(var i=1,iLen=n.length;
i<iLen;i++){n[i].innerHTML=sInfo}}}function _fnFeatureHtmlLength(oSettings){var sName=(oSettings.sTableId==="")?"":'name="'+oSettings.sTableId+'_length"';
var sStdMenu='<select size="1" '+sName+'><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select>';
var nLength=document.createElement("div");if(oSettings.sTableId!==""&&typeof oSettings.aanFeatures.l=="undefined"){nLength.setAttribute("id",oSettings.sTableId+"_length")
}nLength.className=oSettings.oClasses.sLength;nLength.innerHTML=oSettings.oLanguage.sLengthMenu.replace("_MENU_",sStdMenu);
$('select option[value="'+oSettings._iDisplayLength+'"]',nLength).attr("selected",true);
$("select",nLength).change(function(e){var iVal=$(this).val();var n=oSettings.aanFeatures.l;
for(var i=0,iLen=n.length;i<iLen;i++){if(n[i]!=this.parentNode){$("select",n[i]).val(iVal)
}}oSettings._iDisplayLength=parseInt(iVal,10);_fnCalculateEnd(oSettings);if(oSettings._iDisplayEnd==oSettings.aiDisplay.length){oSettings._iDisplayStart=oSettings._iDisplayEnd-oSettings._iDisplayLength;
if(oSettings._iDisplayStart<0){oSettings._iDisplayStart=0}}if(oSettings._iDisplayLength==-1){oSettings._iDisplayStart=0
}_fnDraw(oSettings)});return nLength}function _fnFeatureHtmlProcessing(oSettings){var nProcessing=document.createElement("div");
if(oSettings.sTableId!==""&&typeof oSettings.aanFeatures.r=="undefined"){nProcessing.setAttribute("id",oSettings.sTableId+"_processing")
}nProcessing.innerHTML=oSettings.oLanguage.sProcessing;nProcessing.className=oSettings.oClasses.sProcessing;
oSettings.nTable.parentNode.insertBefore(nProcessing,oSettings.nTable);return nProcessing
}function _fnProcessingDisplay(oSettings,bShow){if(oSettings.oFeatures.bProcessing){var an=oSettings.aanFeatures.r;
for(var i=0,iLen=an.length;i<iLen;i++){an[i].style.visibility=bShow?"visible":"hidden"
}}}function _fnVisibleToColumnIndex(oSettings,iMatch){var iColumn=-1;for(var i=0;
i<oSettings.aoColumns.length;i++){if(oSettings.aoColumns[i].bVisible===true){iColumn++
}if(iColumn==iMatch){return i}}return null}function _fnColumnIndexToVisible(oSettings,iMatch){var iVisible=-1;
for(var i=0;i<oSettings.aoColumns.length;i++){if(oSettings.aoColumns[i].bVisible===true){iVisible++
}if(i==iMatch){return oSettings.aoColumns[i].bVisible===true?iVisible:null}}return null
}function _fnNodeToDataIndex(s,n){for(var i=0,iLen=s.aoData.length;i<iLen;i++){if(s.aoData[i]!==null&&s.aoData[i].nTr==n){return i
}}return null}function _fnVisbleColumns(oS){var iVis=0;for(var i=0;i<oS.aoColumns.length;
i++){if(oS.aoColumns[i].bVisible===true){iVis++}}return iVis}function _fnCalculateEnd(oSettings){if(oSettings.oFeatures.bPaginate===false){oSettings._iDisplayEnd=oSettings.aiDisplay.length
}else{if(oSettings._iDisplayStart+oSettings._iDisplayLength>oSettings.aiDisplay.length||oSettings._iDisplayLength==-1){oSettings._iDisplayEnd=oSettings.aiDisplay.length
}else{oSettings._iDisplayEnd=oSettings._iDisplayStart+oSettings._iDisplayLength}}}function _fnConvertToWidth(sWidth,nParent){if(!sWidth||sWidth===null||sWidth===""){return 0
}if(typeof nParent=="undefined"){nParent=document.getElementsByTagName("body")[0]
}var iWidth;var nTmp=document.createElement("div");nTmp.style.width=sWidth;nParent.appendChild(nTmp);
iWidth=nTmp.offsetWidth;nParent.removeChild(nTmp);return(iWidth)}function _fnCalculateColumnWidths(oSettings){var iTableWidth=oSettings.nTable.offsetWidth;
var iTotalUserIpSize=0;var iTmpWidth;var iVisibleColumns=0;var iColums=oSettings.aoColumns.length;
var i;var oHeaders=$("thead:eq(0)>th",oSettings.nTable);for(i=0;i<iColums;i++){if(oSettings.aoColumns[i].bVisible){iVisibleColumns++;
if(oSettings.aoColumns[i].sWidth!==null){iTmpWidth=_fnConvertToWidth(oSettings.aoColumns[i].sWidth,oSettings.nTable.parentNode);
iTotalUserIpSize+=iTmpWidth;oSettings.aoColumns[i].sWidth=iTmpWidth+"px"}}}if(iColums==oHeaders.length&&iTotalUserIpSize===0&&iVisibleColumns==iColums){for(i=0;
i<oSettings.aoColumns.length;i++){oSettings.aoColumns[i].sWidth=oHeaders[i].offsetWidth+"px"
}}else{var nCalcTmp=oSettings.nTable.cloneNode(false);nCalcTmp.setAttribute("id","");
var sTableTmp='<table class="'+nCalcTmp.className+'">';var sCalcHead="<tr>";var sCalcHtml="<tr>";
for(i=0;i<iColums;i++){if(oSettings.aoColumns[i].bVisible){sCalcHead+="<th>"+oSettings.aoColumns[i].sTitle+"</th>";
if(oSettings.aoColumns[i].sWidth!==null){var sWidth="";if(oSettings.aoColumns[i].sWidth!==null){sWidth=' style="width:'+oSettings.aoColumns[i].sWidth+';"'
}sCalcHtml+="<td"+sWidth+' tag_index="'+i+'">'+fnGetMaxLenString(oSettings,i)+"</td>"
}else{sCalcHtml+='<td tag_index="'+i+'">'+fnGetMaxLenString(oSettings,i)+"</td>"}}}sCalcHead+="</tr>";
sCalcHtml+="</tr>";nCalcTmp=$(sTableTmp+sCalcHead+sCalcHtml+"</table>")[0];nCalcTmp.style.width=iTableWidth+"px";
nCalcTmp.style.visibility="hidden";nCalcTmp.style.position="absolute";oSettings.nTable.parentNode.appendChild(nCalcTmp);
var oNodes=$("tr:eq(1)>td",nCalcTmp);var iIndex;for(i=0;i<oNodes.length;i++){iIndex=oNodes[i].getAttribute("tag_index");
var iContentWidth=$("td",nCalcTmp).eq(i).width();var iSetWidth=oSettings.aoColumns[i].sWidth?oSettings.aoColumns[i].sWidth.slice(0,-2):0;
oSettings.aoColumns[iIndex].sWidth=Math.max(iContentWidth,iSetWidth)+"px"}oSettings.nTable.parentNode.removeChild(nCalcTmp)
}}function fnGetMaxLenString(oSettings,iCol){var iMax=0;var iMaxIndex=-1;for(var i=0;
i<oSettings.aoData.length;i++){if(oSettings.aoData[i]._aData[iCol].length>iMax){iMax=oSettings.aoData[i]._aData[iCol].length;
iMaxIndex=i}}if(iMaxIndex>=0){return oSettings.aoData[iMaxIndex]._aData[iCol]}return""
}function _fnArrayCmp(aArray1,aArray2){if(aArray1.length!=aArray2.length){return 1
}for(var i=0;i<aArray1.length;i++){if(aArray1[i]!=aArray2[i]){return 2}}return 0}function _fnDetectType(sData){var aTypes=_oExt.aTypes;
var iLen=aTypes.length;for(var i=0;i<iLen;i++){var sType=aTypes[i](sData);if(sType!==null){return sType
}}return"string"}function _fnSettingsFromNode(nTable){for(var i=0;i<_aoSettings.length;
i++){if(_aoSettings[i].nTable==nTable){return _aoSettings[i]}}return null}function _fnGetDataMaster(oSettings){var aData=[];
var iLen=oSettings.aoData.length;for(var i=0;i<iLen;i++){if(oSettings.aoData[i]===null){aData.push(null)
}else{aData.push(oSettings.aoData[i]._aData)}}return aData}function _fnGetTrNodes(oSettings){var aNodes=[];
var iLen=oSettings.aoData.length;for(var i=0;i<iLen;i++){if(oSettings.aoData[i]===null){aNodes.push(null)
}else{aNodes.push(oSettings.aoData[i].nTr)}}return aNodes}function _fnGetTdNodes(oSettings){var nTrs=_fnGetTrNodes(oSettings);
var nTds=[],nTd;var anReturn=[];var iCorrector;var iRow,iRows,iColumn,iColumns;for(iRow=0,iRows=nTrs.length;
iRow<iRows;iRow++){nTds=[];for(iColumn=0,iColumns=nTrs[iRow].childNodes.length;iColumn<iColumns;
iColumn++){nTd=nTrs[iRow].childNodes[iColumn];if(nTd.nodeName=="TD"){nTds.push(nTd)
}}iCorrector=0;for(iColumn=0,iColumns=oSettings.aoColumns.length;iColumn<iColumns;
iColumn++){if(oSettings.aoColumns[iColumn].bVisible){anReturn.push(nTds[iColumn-iCorrector])
}else{anReturn.push(oSettings.aoData[iRow]._anHidden[iColumn]);iCorrector++}}}return anReturn
}function _fnEscapeRegex(sVal){var acEscape=["/",".","*","+","?","|","(",")","[","]","{","}","\\","$","^"];
var reReplace=new RegExp("(\\"+acEscape.join("|\\")+")","g");return sVal.replace(reReplace,"\\$1")
}function _fnReOrderIndex(oSettings,sColumns){var aColumns=sColumns.split(",");var aiReturn=[];
for(var i=0,iLen=oSettings.aoColumns.length;i<iLen;i++){for(var j=0;j<iLen;j++){if(oSettings.aoColumns[i].sName==aColumns[j]){aiReturn.push(j);
break}}}return aiReturn}function _fnColumnOrdering(oSettings){var sNames="";for(var i=0,iLen=oSettings.aoColumns.length;
i<iLen;i++){sNames+=oSettings.aoColumns[i].sName+","}if(sNames.length==iLen){return""
}return sNames.slice(0,-1)}function _fnClearTable(oSettings){oSettings.aoData.length=0;
oSettings.aiDisplayMaster.length=0;oSettings.aiDisplay.length=0;_fnCalculateEnd(oSettings)
}function _fnSaveState(oSettings){if(!oSettings.oFeatures.bStateSave){return}var i;
var sValue="{";sValue+='"iStart": '+oSettings._iDisplayStart+",";sValue+='"iEnd": '+oSettings._iDisplayEnd+",";
sValue+='"iLength": '+oSettings._iDisplayLength+",";sValue+='"sFilter": "'+oSettings.oPreviousSearch.sSearch.replace('"','\\"')+'",';
sValue+='"sFilterEsc": '+oSettings.oPreviousSearch.bEscapeRegex+",";sValue+='"aaSorting": [ ';
for(i=0;i<oSettings.aaSorting.length;i++){sValue+="["+oSettings.aaSorting[i][0]+",'"+oSettings.aaSorting[i][1]+"'],"
}sValue=sValue.substring(0,sValue.length-1);sValue+="],";sValue+='"aaSearchCols": [ ';
for(i=0;i<oSettings.aoPreSearchCols.length;i++){sValue+="['"+oSettings.aoPreSearchCols[i].sSearch.replace("'","'")+"',"+oSettings.aoPreSearchCols[i].bEscapeRegex+"],"
}sValue=sValue.substring(0,sValue.length-1);sValue+="],";sValue+='"abVisCols": [ ';
for(i=0;i<oSettings.aoColumns.length;i++){sValue+=oSettings.aoColumns[i].bVisible+","
}sValue=sValue.substring(0,sValue.length-1);sValue+="]";sValue+="}";_fnCreateCookie("SpryMedia_DataTables_"+oSettings.sInstance,sValue,oSettings.iCookieDuration)
}function _fnLoadState(oSettings,oInit){if(!oSettings.oFeatures.bStateSave){return
}var oData;var sData=_fnReadCookie("SpryMedia_DataTables_"+oSettings.sInstance);if(sData!==null&&sData!==""){try{if(typeof JSON=="object"&&typeof JSON.parse=="function"){oData=JSON.parse(sData.replace(/'/g,'"'))
}else{oData=eval("("+sData+")")}}catch(e){return}oSettings._iDisplayStart=oData.iStart;
oSettings.iInitDisplayStart=oData.iStart;oSettings._iDisplayEnd=oData.iEnd;oSettings._iDisplayLength=oData.iLength;
oSettings.oPreviousSearch.sSearch=oData.sFilter;oSettings.aaSorting=oData.aaSorting.slice();
oSettings.saved_aaSorting=oData.aaSorting.slice();if(typeof oData.sFilterEsc!="undefined"){oSettings.oPreviousSearch.bEscapeRegex=oData.sFilterEsc
}if(typeof oData.aaSearchCols!="undefined"){for(var i=0;i<oData.aaSearchCols.length;
i++){oSettings.aoPreSearchCols[i]={sSearch:oData.aaSearchCols[i][0],bEscapeRegex:oData.aaSearchCols[i][1]}
}}if(typeof oData.abVisCols!="undefined"){oInit.saved_aoColumns=[];for(i=0;i<oData.abVisCols.length;
i++){oInit.saved_aoColumns[i]={};oInit.saved_aoColumns[i].bVisible=oData.abVisCols[i]
}}}}function _fnCreateCookie(sName,sValue,iSecs){var date=new Date();date.setTime(date.getTime()+(iSecs*1000));
sName+="_"+window.location.pathname.replace(/[\/:]/g,"").toLowerCase();document.cookie=sName+"="+encodeURIComponent(sValue)+"; expires="+date.toGMTString()+"; path=/"
}function _fnReadCookie(sName){var sNameEQ=sName+"_"+window.location.pathname.replace(/[\/:]/g,"").toLowerCase()+"=";
var sCookieContents=document.cookie.split(";");for(var i=0;i<sCookieContents.length;
i++){var c=sCookieContents[i];while(c.charAt(0)==" "){c=c.substring(1,c.length)}if(c.indexOf(sNameEQ)===0){return decodeURIComponent(c.substring(sNameEQ.length,c.length))
}}return null}function _fnGetUniqueThs(nThead){var nTrs=nThead.getElementsByTagName("tr");
if(nTrs.length==1){return nTrs[0].getElementsByTagName("th")}var aLayout=[],aReturn=[];
var ROWSPAN=2,COLSPAN=3,TDELEM=4;var i,j,k,iLen,jLen,iColumnShifted;var fnShiftCol=function(a,i,j){while(typeof a[i][j]!="undefined"){j++
}return j};var fnAddRow=function(i){if(typeof aLayout[i]=="undefined"){aLayout[i]=[]
}};for(i=0,iLen=nTrs.length;i<iLen;i++){fnAddRow(i);var iColumn=0;var nTds=[];for(j=0,jLen=nTrs[i].childNodes.length;
j<jLen;j++){if(nTrs[i].childNodes[j].nodeName=="TD"||nTrs[i].childNodes[j].nodeName=="TH"){nTds.push(nTrs[i].childNodes[j])
}}for(j=0,jLen=nTds.length;j<jLen;j++){var iColspan=nTds[j].getAttribute("colspan")*1;
var iRowspan=nTds[j].getAttribute("rowspan")*1;if(!iColspan||iColspan===0||iColspan===1){iColumnShifted=fnShiftCol(aLayout,i,iColumn);
aLayout[i][iColumnShifted]=(nTds[j].nodeName=="TD")?TDELEM:nTds[j];if(iRowspan||iRowspan===0||iRowspan===1){for(k=1;
k<iRowspan;k++){fnAddRow(i+k);aLayout[i+k][iColumnShifted]=ROWSPAN}}iColumn++}else{iColumnShifted=fnShiftCol(aLayout,i,iColumn);
for(k=0;k<iColspan;k++){aLayout[i][iColumnShifted+k]=COLSPAN}iColumn+=iColspan}}}for(i=0,iLen=aLayout[0].length;
i<iLen;i++){for(j=0,jLen=aLayout.length;j<jLen;j++){if(typeof aLayout[j][i]=="object"){aReturn.push(aLayout[j][i])
}}}return aReturn}function _fnMap(oRet,oSrc,sName,sMappedName){if(typeof sMappedName=="undefined"){sMappedName=sName
}if(typeof oSrc[sName]!="undefined"){oRet[sMappedName]=oSrc[sName]}}this.oApi._fnInitalise=_fnInitalise;
this.oApi._fnLanguageProcess=_fnLanguageProcess;this.oApi._fnAddColumn=_fnAddColumn;
this.oApi._fnAddData=_fnAddData;this.oApi._fnGatherData=_fnGatherData;this.oApi._fnDrawHead=_fnDrawHead;
this.oApi._fnDraw=_fnDraw;this.oApi._fnAjaxUpdate=_fnAjaxUpdate;this.oApi._fnAddOptionsHtml=_fnAddOptionsHtml;
this.oApi._fnFeatureHtmlFilter=_fnFeatureHtmlFilter;this.oApi._fnFeatureHtmlInfo=_fnFeatureHtmlInfo;
this.oApi._fnFeatureHtmlPaginate=_fnFeatureHtmlPaginate;this.oApi._fnPageChange=_fnPageChange;
this.oApi._fnFeatureHtmlLength=_fnFeatureHtmlLength;this.oApi._fnFeatureHtmlProcessing=_fnFeatureHtmlProcessing;
this.oApi._fnProcessingDisplay=_fnProcessingDisplay;this.oApi._fnFilterComplete=_fnFilterComplete;
this.oApi._fnFilterColumn=_fnFilterColumn;this.oApi._fnFilter=_fnFilter;this.oApi._fnSortingClasses=_fnSortingClasses;
this.oApi._fnVisibleToColumnIndex=_fnVisibleToColumnIndex;this.oApi._fnColumnIndexToVisible=_fnColumnIndexToVisible;
this.oApi._fnNodeToDataIndex=_fnNodeToDataIndex;this.oApi._fnVisbleColumns=_fnVisbleColumns;
this.oApi._fnBuildSearchArray=_fnBuildSearchArray;this.oApi._fnDataToSearch=_fnDataToSearch;
this.oApi._fnCalculateEnd=_fnCalculateEnd;this.oApi._fnConvertToWidth=_fnConvertToWidth;
this.oApi._fnCalculateColumnWidths=_fnCalculateColumnWidths;this.oApi._fnArrayCmp=_fnArrayCmp;
this.oApi._fnDetectType=_fnDetectType;this.oApi._fnGetDataMaster=_fnGetDataMaster;
this.oApi._fnGetTrNodes=_fnGetTrNodes;this.oApi._fnGetTdNodes=_fnGetTdNodes;this.oApi._fnEscapeRegex=_fnEscapeRegex;
this.oApi._fnReOrderIndex=_fnReOrderIndex;this.oApi._fnColumnOrdering=_fnColumnOrdering;
this.oApi._fnClearTable=_fnClearTable;this.oApi._fnSaveState=_fnSaveState;this.oApi._fnLoadState=_fnLoadState;
this.oApi._fnCreateCookie=_fnCreateCookie;this.oApi._fnReadCookie=_fnReadCookie;this.oApi._fnGetUniqueThs=_fnGetUniqueThs;
this.oApi._fnReDraw=_fnReDraw;var _that=this;return this.each(function(){var i=0,iLen,j,jLen;
for(i=0,iLen=_aoSettings.length;i<iLen;i++){if(_aoSettings[i].nTable==this){alert("DataTables warning: Unable to re-initialise DataTable. Please use the API to make any configuration changes required.");
return _aoSettings[i]}}var oSettings=new classSettings();_aoSettings.push(oSettings);
var bInitHandedOff=false;var bUsePassedData=false;var sId=this.getAttribute("id");
if(sId!==null){oSettings.sTableId=sId;oSettings.sInstance=sId}else{oSettings.sInstance=_oExt._oExternConfig.iNextUnique++
}oSettings.nTable=this;oSettings.oApi=_that.oApi;if(typeof oInit!="undefined"&&oInit!==null){_fnMap(oSettings.oFeatures,oInit,"bPaginate");
_fnMap(oSettings.oFeatures,oInit,"bLengthChange");_fnMap(oSettings.oFeatures,oInit,"bFilter");
_fnMap(oSettings.oFeatures,oInit,"bSort");_fnMap(oSettings.oFeatures,oInit,"bInfo");
_fnMap(oSettings.oFeatures,oInit,"bProcessing");_fnMap(oSettings.oFeatures,oInit,"bAutoWidth");
_fnMap(oSettings.oFeatures,oInit,"bSortClasses");_fnMap(oSettings.oFeatures,oInit,"bServerSide");
_fnMap(oSettings,oInit,"asStripClasses");_fnMap(oSettings,oInit,"fnRowCallback");
_fnMap(oSettings,oInit,"fnHeaderCallback");_fnMap(oSettings,oInit,"fnFooterCallback");
_fnMap(oSettings,oInit,"fnInitComplete");_fnMap(oSettings,oInit,"fnServerData");_fnMap(oSettings,oInit,"aaSorting");
_fnMap(oSettings,oInit,"aaSortingFixed");_fnMap(oSettings,oInit,"sPaginationType");
_fnMap(oSettings,oInit,"sAjaxSource");_fnMap(oSettings,oInit,"iCookieDuration");_fnMap(oSettings,oInit,"sDom");
_fnMap(oSettings,oInit,"oSearch","oPreviousSearch");_fnMap(oSettings,oInit,"aoSearchCols","aoPreSearchCols");
_fnMap(oSettings,oInit,"iDisplayLength","_iDisplayLength");_fnMap(oSettings,oInit,"bJQueryUI","bJUI");
if(typeof oInit.fnDrawCallback=="function"){oSettings.aoDrawCallback.push({fn:oInit.fnDrawCallback,sName:"user"})
}if(oSettings.oFeatures.bServerSide&&oSettings.oFeatures.bSort&&oSettings.oFeatures.bSortClasses){oSettings.aoDrawCallback.push({fn:_fnSortingClasses,sName:"server_side_sort_classes"})
}if(typeof oInit.bJQueryUI!="undefined"&&oInit.bJQueryUI){oSettings.oClasses=_oExt.oJUIClasses;
if(typeof oInit.sDom=="undefined"){oSettings.sDom='<"H"lfr>t<"F"ip>'}}if(typeof oInit.iDisplayStart!="undefined"&&typeof oSettings.iInitDisplayStart=="undefined"){oSettings.iInitDisplayStart=oInit.iDisplayStart;
oSettings._iDisplayStart=oInit.iDisplayStart}if(typeof oInit.bStateSave!="undefined"){oSettings.oFeatures.bStateSave=oInit.bStateSave;
_fnLoadState(oSettings,oInit);oSettings.aoDrawCallback.push({fn:_fnSaveState,sName:"state_save"})
}if(typeof oInit.aaData!="undefined"){bUsePassedData=true}if(typeof oInit!="undefined"&&typeof oInit.aoData!="undefined"){oInit.aoColumns=oInit.aoData
}if(typeof oInit.oLanguage!="undefined"){if(typeof oInit.oLanguage.sUrl!="undefined"&&oInit.oLanguage.sUrl!==""){oSettings.oLanguage.sUrl=oInit.oLanguage.sUrl;
$.getJSON(oSettings.oLanguage.sUrl,null,function(json){_fnLanguageProcess(oSettings,json,true)
});bInitHandedOff=true}else{_fnLanguageProcess(oSettings,oInit.oLanguage,false)}}}else{oInit={}
}if(typeof oInit.asStripClasses=="undefined"){oSettings.asStripClasses.push(oSettings.oClasses.sStripOdd);
oSettings.asStripClasses.push(oSettings.oClasses.sStripEven)}var nThead=this.getElementsByTagName("thead");
var nThs=nThead.length===0?null:_fnGetUniqueThs(nThead[0]);var bUseCols=typeof oInit.aoColumns!="undefined";
for(i=0,iLen=bUseCols?oInit.aoColumns.length:nThs.length;i<iLen;i++){var oCol=bUseCols?oInit.aoColumns[i]:null;
var nTh=nThs?nThs[i]:null;if(typeof oInit.saved_aoColumns!="undefined"&&oInit.saved_aoColumns.length==iLen){if(oCol===null){oCol={}
}oCol.bVisible=oInit.saved_aoColumns[i].bVisible}_fnAddColumn(oSettings,oCol,nTh)
}for(i=0,iLen=oSettings.aaSorting.length;i<iLen;i++){var oColumn=oSettings.aoColumns[oSettings.aaSorting[i][0]];
if(typeof oSettings.aaSorting[i][2]=="undefined"){oSettings.aaSorting[i][2]=0}if(typeof oInit.aaSorting=="undefined"&&typeof oSettings.saved_aaSorting=="undefined"){oSettings.aaSorting[i][1]=oColumn.asSorting[0]
}for(j=0,jLen=oColumn.asSorting.length;j<jLen;j++){if(oSettings.aaSorting[i][1]==oColumn.asSorting[j]){oSettings.aaSorting[i][2]=j;
break}}}if(this.getElementsByTagName("thead").length===0){this.appendChild(document.createElement("thead"))
}if(this.getElementsByTagName("tbody").length===0){this.appendChild(document.createElement("tbody"))
}if(bUsePassedData){for(i=0;i<oInit.aaData.length;i++){_fnAddData(oSettings,oInit.aaData[i])
}}else{_fnGatherData(oSettings)}oSettings.aiDisplay=oSettings.aiDisplayMaster.slice();
if(oSettings.oFeatures.bAutoWidth){_fnCalculateColumnWidths(oSettings)}oSettings.bInitialised=true;
if(bInitHandedOff===false){_fnInitalise(oSettings)}})}})(jQuery);;/**
 * @file
 *    Defines the jQuery.dashboard() plugin.
 *
 * Uses jQuery 1.3, jQuery UI 1.6 and several jQuery UI extensions, most of all Sortable
 *    http://visualjquery.com/
 *    http://docs.jquery.com/UI/Sortable
 *    http://ui.jquery.com/download
 *      Sortable
 *      Draggable
 *      UI Core
 *
 * Released under the GNU General Public License.  See LICENSE.txt.
 */

(function($) { // Create closure.
  // Constructor for dashboard object.
  $.fn.dashboard = function(options) {
    // Public properties of dashboard.
    var dashboard = {};
    dashboard.element = this.empty();
    dashboard.ready = false;
    dashboard.columns = Array();
    dashboard.widgets = Array();
    // End of public properties of dashboard.

    /**
     * Public methods of dashboard.
     */

    // Saves the order of widgets for all columns including the widget.minimized status to options.ajaxCallbacks.saveColumns.
    dashboard.saveColumns = function() {
      // Update the display status of the empty placeholders.
      for (var c in dashboard.columns) {
        var col = dashboard.columns[c];
       if ( typeof col == 'object' ) {
			// Are there any visible children of the column (excluding the empty placeholder)?
			if (col.element.children(':visible').not(col.emptyPlaceholder).length > 0) {
			  col.emptyPlaceholder.hide();
			}
			else {
			  col.emptyPlaceholder.show();
			}
		}
	  }

      // Don't save any changes to the server unless the dashboard has finished initiating.
      if (!dashboard.ready) {
        return;
      }

      // Build a list of params to post to the server.
      var params = {};

      // For each column...
      for (var c in dashboard.columns) {

        // IDs of the sortable elements in this column.
        if( typeof dashboard.columns[c] == 'object' ) var ids = dashboard.columns[c].element.sortable('toArray');

        // For each id...
        for (var w in ids) {
          // Chop 'widget-' off of the front so that we have the real widget id.
          if( typeof ids[w] == 'string' ) var id = ids[w].substring('widget-'.length);

          // Add one flat property to the params object that will look like an array element to the PHP server.
          // Unfortunately jQuery doesn't do this for us.
          if ( typeof dashboard.widgets[id] == 'object' ) params['columns[' + c + '][' + id + ']'] = (dashboard.widgets[id].minimized ? '1' : '0');
        }
      }

      // The ajaxCallback settings overwrite any duplicate properties.
      $.extend(params, opts.ajaxCallbacks.saveColumns.data);
      $.post(opts.ajaxCallbacks.saveColumns.url, params, function(response, status) {
        invokeCallback(opts.callbacks.saveColumns, dashboard);

        // Log the response to aid server-side debugging.
        if (window.console && console.log) {
          console.log(response);
        }
      });
    };

    // Puts the dashboard into full screen mode, saving element for when the user exits full-screen mode.
    // Does not add element to the DOM – this is the caller's responsibility.
    // Does show and hide element though.
    dashboard.enterFullscreen = function(element) {
      // Hide the columns.
      for (var c in dashboard.columns) {
       if ( typeof dashboard.columns[c] == 'object' ) dashboard.columns[c].element.hide();
      }

      if (!dashboard.fullscreen) {
        // Initialize.
        var markup = '<a id="full-screen-header" class="full-screen-close-icon">' + opts.fullscreenHeaderInner + '</a>';
        dashboard.fullscreen = {
          headerElement: $(markup).prependTo(dashboard.element).click(dashboard.exitFullscreen).hide()
        };
      }

      dashboard.fullscreen.headerElement.slideDown();
      dashboard.fullscreen.currentElement = element.show();
      dashboard.fullscreen.displayed = true;
      invokeCallback(opts.callbacks.enterFullscreen, dashboard, dashboard.fullscreen.currentElement);
    };

    // Takes the dashboard out of full screen mode, hiding the active fullscreen element.
    dashboard.exitFullscreen = function() {
      if (!dashboard.fullscreen.displayed) {
        return;
      }

      dashboard.fullscreen.headerElement.slideUp();
      dashboard.fullscreen.currentElement.hide();
      dashboard.fullscreen.displayed = false;

      // Show the columns.
      for (var c in dashboard.columns) {
       if ( typeof dashboard.columns[c] == 'object' ) dashboard.columns[c].element.show();
      }

      invokeCallback(opts.callbacks.exitFullscreen, dashboard, dashboard.fullscreen.currentElement);
    };
    // End of public methods of dashboard.

    /**
     * Private properties of dashboard.
     */
    
    // Used to determine whether there are any incomplete ajax requests pending initialization of the dashboard.
    var asynchronousRequestCounter = 0;

    // Used to determine whether two resort events are resulting from the same UI event.
    var currentReSortEvent = null;

    // Merge in the caller's options with the defaults.
    var opts = $.extend({}, $.fn.dashboard.defaults, options);

    // Execution 'forks' here and restarts in init().  Tell the user we're busy with a throbber.
    var throbber = $(opts.throbberMarkup).appendTo(dashboard.element);
    $.getJSON(opts.ajaxCallbacks.getWidgetsByColumn.url, opts.ajaxCallbacks.getWidgetsByColumn.data, init);
    asynchronousRequestCounter++;
    return dashboard;
    // End of constructor and private properties for dashboard object.

    /**
     * Private methods of dashboard.
     */

    // Ajax callback for getWidgetsByColumn.
    function init(widgets, status) {
      asynchronousRequestCounter--;
      throbber.remove();
      var markup = '<li class="empty-placeholder">' + opts.emptyPlaceholderInner + '</li>';

      // Build the dashboard in the DOM.  For each column...
      // (Don't iterate on widgets since this will break badly if the dataset has empty columns.)
      var emptyDashboard = true;
      for (var c = 0; c < opts.columns; c++) {
        // Save the column to both the public scope for external accessibility and the local scope for readability.
        var col = dashboard.columns[c] = {
          initialWidgets: Array(),
          element: $('<ul id="column-' + c + '" class="column column-' + c + '"></ul>').appendTo(dashboard.element)
        };
        
        // Add the empty placeholder now, hide it and save it.
        col.emptyPlaceholder = $(markup).appendTo(col.element).hide();

        // For each widget in this column.
        for (var id in widgets[c]) {
          // Build a new widget object and save it to various publicly accessible places.
          col.initialWidgets[id] = dashboard.widgets[id] = widget({
            id: id,
            element: $('<li class="widget"></li>').appendTo(col.element),
            initialColumn: col,
            minimized: ( widgets[c][id] > 0  ? true : false )
          });
          
          //set empty Dashboard to false
          emptyDashboard = false;
        }
      }

      if ( emptyDashboard ) {
          emptyDashboardCondition( );
      }
      
      invokeCallback(opts.callbacks.init, dashboard);
    }

    // function that is called when dashboard is empty
    function emptyDashboardCondition( ) {
        cj(".show-refresh").hide( );
        cj("#empty-message").show( );
    }
    
    // Contructors for each widget call this when initialization has finished so that dashboard can complete it's intitialization.
    function completeInit() {
      // Don't do anything if any widgets are waiting for ajax requests to complete in order to finish initialization.
      if (asynchronousRequestCounter > 0) {
        return;
      }

      // Make widgets sortable across columns.
      dashboard.sortableElement = $('.column').sortable({
        connectWith: ['.column'],

        // The class of the element by which widgets are draggable.
        handle: '.widget-header',

        // The class of placeholder elements (the 'ghost' widget showing where the dragged item would land if released now.)
        placeholder: 'placeholder',

        // This worked in jQuery UI 1.6 rc5, but broke in rc6.
        // @todo: Test this and/or report sortable's opacity bug.
        // opacity: 0.2,

        // Maks sure that only widgets are sortable, and not empty placeholders.
        items: '> .widget',
        forcePlaceholderSize: true,

        // Callback functions.
        update: resorted,
        start: hideEmptyPlaceholders
      });

      // Update empty placeholders.
      dashboard.saveColumns();
      dashboard.ready = true;
      invokeCallback(opts.callbacks.ready, dashboard);
    }

    // Callback for when any list has changed (and the user has finished resorting).
    function resorted(e, ui) {
      // Only do anything if we haven't already handled resorts based on changes from this UI DOM event.
      // (resorted() gets invoked once for each list when an item is moved from one to another.)
      if (!currentReSortEvent || e.originalEvent != currentReSortEvent) {
        currentReSortEvent = e.originalEvent;
        dashboard.saveColumns();
      }
    }

    // Callback for when a user starts resorting a list.  Hides all the empty placeholders.
    function hideEmptyPlaceholders(e, ui) {
      for (var c in dashboard.columns) {
        if( typeof dashboard.columns[c] == 'object ' ) dashboard.columns[c].emptyPlaceholder.hide();
      }
    }

    // @todo use an event library to register, bind to and invoke events.
    //  @param callback is a function.
    //  @param theThis is the context given to that function when it executes.  It becomes 'this' inside of that function.
    function invokeCallback(callback, theThis, parameterOne) {
      if (callback) {
        callback.call(theThis, parameterOne);
      }
    }

    /**
     * widget object
     *    Private sub-class of dashboard
     * Constructor starts
     */
    function widget(widget) {
      // Merge default options with the options defined for this widget.
      widget = $.extend({}, $.fn.dashboard.widget.defaults, widget);

      /**
       * Public methods of widget.
       */

      // Toggles the minimize() & maximize() methods.
      widget.toggleMinimize = function() {
        if (widget.minimized) {
          widget.maximize();
        }
        else {
          widget.minimize();
        }

        widget.hideSettings();
        dashboard.saveColumns();
      };
      widget.minimize = function() {
        $('.widget-content', widget.element).slideUp(opts.animationSpeed);
        $(widget.controls.minimize.element).addClass( 'maximize-icon' );
        $(widget.controls.minimize.element).removeClass( 'minimize-icon' );
        widget.minimized = true;
      };
      widget.maximize = function() {
        $('.widget-content', widget.element).slideDown(opts.animationSpeed);
        $(widget.controls.minimize.element).removeClass( 'maximize-icon' );
        $(widget.controls.minimize.element).addClass( 'minimize-icon' );
        widget.minimized = false;
      };

      // Toggles whether the widget is in settings-display mode or not.
      widget.toggleSettings = function() {
        if (widget.settings.displayed) {
          // Widgets always exit settings into maximized state.
          widget.maximize();
          widget.hideSettings();
          invokeCallback(opts.widgetCallbacks.hideSettings, widget);
        }
        else {
          widget.minimize();
          widget.showSettings();
          invokeCallback(opts.widgetCallbacks.showSettings, widget);
        }
      };
      widget.showSettings = function() {
        if (widget.settings.element) {
          widget.settings.element.show();

          // Settings are loaded via AJAX.  Only execute the script if the settings have been loaded.
          if (widget.settings.ready) {
            getJavascript(widget.settings.script);
          }
        }
        else {
          // Settings have not been initialized.  Do so now.
          initSettings();
        }
        widget.settings.displayed = true;
      };
      widget.hideSettings = function() {
        if (widget.settings.element) {
          widget.settings.element.hide();
        }
        widget.settings.displayed = false;
      };
      widget.saveSettings = function() {
        // Build list of parameters to POST to server.
        var params = {};
        // serializeArray() returns an array of objects.  Process it.
        var fields = widget.settings.element.serializeArray();
        for (var i in fields) {
          var field = fields[i];
          // Put the values into flat object properties that PHP will parse into an array server-side.
          // (Unfortunately jQuery doesn't do this)
          params['settings[' + field.name + ']'] = field.value;
        }

        // Things get messy here.
        // @todo Refactor to use currentState and targetedState properties to determine what needs 
        // to be done to get to any desired state on any UI or AJAX event – since these don't always 
        // match.  
        // E.g.  When a user starts a new UI event before the Ajax event handler from a previous 
        // UI event gets invoked.

        // Hide the settings first of all.
        widget.toggleSettings();
        // Save the real settings element so that we can restore the reference later.
        var settingsElement = widget.settings.element;
        // Empty the settings form.
        widget.settings.innerElement.empty();
        initThrobber();
        // So that showSettings() and hideSettings() can do SOMETHING, without showing the empty settings form.
        widget.settings.element = widget.throbber.hide();
        widget.settings.ready = false;

        // Save the settings to the server.
        $.extend(params, opts.ajaxCallbacks.widgetSettings.data, { id: widget.id });
        $.post(opts.ajaxCallbacks.widgetSettings.url, params, function(response, status) {
          // Merge the response into widget.settings.
          $.extend(widget.settings, response);
          // Restore the reference to the real settings element.
          widget.settings.element = settingsElement;
          // Make sure the settings form is empty and add the updated settings form.
          widget.settings.innerElement.empty().append(widget.settings.markup);
          widget.settings.ready = true;

          // Did the user already jump back into settings-display mode before we could finish reloading the settings form?
          if (widget.settings.displayed) {
            // Ooops!  We had better take care of hiding the throbber and showing the settings form then.
            widget.throbber.hide();
            widget.showSettings();
            invokeCallback(opts.widgetCallbacks.saveSettings, dashboard);
          }
        }, 'json');

        // Don't let form submittal bubble up.
        return false;
      };

      widget.enterFullscreen = function() {
        // Make sure the widget actually supports full screen mode.
        if (!widget.fullscreen) {
          return;
        }

        if (!widget.fullscreen.element) {
          // Initialize the full screen element for this widget.
          var markup = '<div id="widget-' + widget.id + '-full-screen">' + widget.fullscreen + '</div>';
          
          // Overwrite the widget.fullscreen string.
          widget.fullscreen = {
            initialMarkup: widget.fullscreen,
            element: $(markup).appendTo(dashboard.element)
          };

          getJavascript(widget.fullscreenInitScript);
        }

        // Let dashboard.enterFullscreen() do the heavy lifting.
        dashboard.enterFullscreen(widget.fullscreen.element);
        getJavascript(widget.fullscreenScript);
        widget.fullscreen.displayed = true;
      };
      // Exit fullscreen mode.
      widget.exitFullscreen = function() {
        // This is just a wrapper for dashboard.exitFullscreen() which does the heavy lifting.
        dashboard.exitFullscreen();
      };

      // Adds controls to a widget.  id is for internal use and image file name in images/dashboard/ (a .gif).
      widget.addControl = function(id, control) {
        //var markup = '<a class="widget-icon ' + id + '-icon"><img src="images/dashboard/' + id + '.gif" alt="' + control.description + '" /></a>';
        var markup = '<a class="widget-icon ' + id + '-icon" alt="' + control.description + '" title="' + control.description + '"></a>';    
        control.element = $(markup).prependTo($('.widget-controls', widget.element)).click(control.callback);
      };

      // An external method used only by and from external scripts to reload content.  Not invoked or used internally.
      // The widget must provide the script that executes this, as well as the script that invokes it.
      widget.reloadContent = function() {
        getJavascript(widget.reloadContentScript);
        invokeCallback(opts.widgetCallbacks.reloadContent, widget);
      };

      // Removes the widget from the dashboard, and saves columns.
      widget.remove = function() {
          if ( confirm( 'Are you sure you want to remove "' + widget.title + '"?') ) {  
              invokeCallback(opts.widgetCallbacks.remove, widget);
              widget.element.fadeOut(opts.animationSpeed, function() {
                  $(this).remove();
                  dashboard.saveColumns();
              });
          }
      };
      // End public methods of widget.

      /**
       * Public properties of widget.
       */

      // Default controls.  External script can add more with widget.addControls()
      widget.controls = {
        settings: {
          description: 'Configure this dashlet',
          callback: widget.toggleSettings
        },
        minimize: {
          description: 'Collapse or expand this dashlet',
          callback: widget.toggleMinimize
        },
        fullscreen: {
          description: 'View this dashlet in full screen mode',
          callback: widget.enterFullscreen
        },
        close: {
          description: 'Remove this dashlet from your dashboard',
          callback: widget.remove
        }
      };
      // End public properties of widget.

      /**
       * Private properties of widget.
       */

      // We're gonna 'fork' execution again, so let's tell the user to hold with us till the AJAX callback gets invoked.
      var throbber = $(opts.throbberMarkup).appendTo(widget.element);
      var params = $.extend({}, opts.ajaxCallbacks.getWidget.data, {id: widget.id});
      $.getJSON(opts.ajaxCallbacks.getWidget.url, params, init);

      // Help dashboard track whether we've got any outstanding requests on which initialization is pending.
      asynchronousRequestCounter++;
      return widget;
      // End of private properties of widget.

      /**
       * Private methods of widget.
       */

      // Ajax callback for widget initialization.
      function init(data, status) {
        asynchronousRequestCounter--;
        $.extend(widget, data);

        // Delete controls that don't apply to this widget.
        if (!widget.settings) {
          delete widget.controls.settings;
        }
        if (!widget.fullscreen) {
          delete widget.controls.fullscreen;
        }

        widget.element.attr('id', 'widget-' + widget.id).addClass(widget.classes);
        throbber.remove();
        // Build and add the widget's DOM element.
        $(widgetHTML()).appendTo(widget.element);
        // Save the content element so that external scripts can reload it easily.
        widget.contentElement = $('.widget-content', widget.element);
        $.each(widget.controls, widget.addControl);

        // Switch the initial state so that it initializes to the correct state.
        widget.minimized = !widget.minimized;
        widget.toggleMinimize();
        getJavascript(widget.initScript);
        invokeCallback(opts.widgetCallbacks.get, widget);

        // completeInit() is a private method of the dashboard.  Let it complete initialization of the dashboard.
        completeInit();
      }

      // Builds inner HTML for widgets.
      function widgetHTML() {
        var html = '';
        html += '<div class="widget-wrapper">';
        html += '  <div class="widget-controls"><h3 class="widget-header">' + widget.title + '</h3></div>';
        //html += '  <div class="widget-header">' + widget.title + '</div>';
        html += '  <div class="widget-content">' + widget.content + '</div>';
        html += '</div>';
        return html;
      }

      // Initializes a widgets settings pane.
      function initSettings() {
        // Overwrite widget.settings (boolean).
        initThrobber();
        widget.settings = {
          element: widget.throbber.show(),
          ready: false
        };

        // Get the settings markup and script executables for this widget.
        var params = $.extend({}, opts.ajaxCallbacks.widgetSettings.data, { id: widget.id });
        $.getJSON(opts.ajaxCallbacks.widgetSettings.url, params, function(response, status) {
          $.extend(widget.settings, response);
          // Build and add the settings form to the DOM.  Bind the form's submit event handler/callback.
          widget.settings.element = $(widgetSettingsHTML()).appendTo($('.widget-wrapper', widget.element)).submit(widget.saveSettings);
          // Bind the cancel button's event handler too.
          widget.settings.cancelButton = $('.widget-settings-cancel', widget.settings.element).click(cancelEditSettings);
          // Build and add the inner form elements from the HTML markup provided in the AJAX data.
          widget.settings.innerElement = $('.widget-settings-inner', widget.settings.element).append(widget.settings.markup);
          widget.settings.ready = true;

          if (widget.settings.displayed) {
            // If the user hasn't clicked away from the settings pane, then display the form.
            widget.throbber.hide();
            widget.showSettings();
          }

          getJavascript(widget.settings.initScript);
        });
      }

      // Builds HTML for widget settings forms.
      function widgetSettingsHTML() {
        var html = '';
        html += '<form class="widget-settings">';
        html += '  <div class="widget-settings-inner"></div>';
        html += '  <div class="widget-settings-buttons">';
        html += '    <input id="' + widget.id + '-settings-save" class="widget-settings-save" value="Save" type="submit" />';
        html += '    <input id="' + widget.id + '-settings-cancel" class="widget-settings-cancel" value="Cancel" type="submit" />';
        html += '  </div>';
        html += '</form>';
        return html;
      }

      // Initializes a generic widget content throbber, for use by settings form and external scripts.
      function initThrobber() {
        if (!widget.throbber) {
          widget.throbber = $(opts.throbberMarkup).appendTo($('.widget-wrapper', widget.element));
        }
      };

      // Event handler/callback for cancel button clicks.
      // @todo test this gets caught by all browsers when the cancel button is 'clicked' via the keyboard.
      function cancelEditSettings() {
        widget.toggleSettings();
        return false;
      };

      // Helper function to execute external script on the server.
      // @todo It would be nice to provide some context to the script.  How?
      function getJavascript(url) {
        if (url) {
          $.getScript(url);
        }
      }
    };
  };

  // Public static properties of dashboard.  Default settings.
  $.fn.dashboard.defaults = {
    columns: 2,
    emptyPlaceholderInner: 'There are no dashlets in this column of your dashboard.',
    fullscreenHeaderInner: 'Back to dashboard mode',
    throbberMarkup: '<div class="crm-loading-element">Loading...</div>',
    animationSpeed: 200,
    callbacks: {},
    widgetCallbacks: {}
  };

  // Default widget settings.
  $.fn.dashboard.widget = {
    defaults: {
      minimized: false,
      settings: false,
      fullscreen: false
    }
  };
})(jQuery); // end of closure
;﻿/**
 * jQuery.FormNavigate.js
 * jQuery Form onChange Navigate Confirmation plugin
 * Browser Compatibility : IE 6.0, 7.0, 8.0; Firefox 2.0+;  Safari 3+; Opera 9+; Chrome 1+;
 *
 * Copyright (c) 2009 Law Ding Yong
 * 
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * See the file license.txt for copying permission.
 */
 
 /**
  * Documentation :
  * ==================
  *
  * How to Use:
  * $("YourForm").FormNavigate("YourMessage");
  *  -- "YourForm" as Your Form ID $("#form") or any method to grab your form
  *  -- "YourMessage" as Your onBeforeUnload Prompt Message Here
  *
  * This plugin handles onchange of input type of text, textarea, password, radio, checkbox, select and file to toggle on and off of window.onbeforeunload event.
  * Users are able to configure the custom onBeforeUnload message.
  */
var global_formNavigate = true;		// Js Global Variable for onChange Flag
(function($){
    $.fn.FormNavigate = function(message) {
        window.onbeforeunload = confirmExit;  
        function confirmExit( event ) {  
            if (global_formNavigate == true) {  event.cancelBubble = true;  }  else  { return message;  }
        }
        $(this+ ":input[type=text], :input[type='textarea'], :input[type='password'], :input[type='radio'], :input[type='checkbox'], :input[type='file'], select").change(function(){
            global_formNavigate = false;
        });
		//to handle back button
		$(this+ ":input[type='textarea']").keyup(function(){ 
			global_formNavigate = false; 
		}); 
        $(this+ ":submit").click(function(){
            global_formNavigate = true;
        });
        $(".token-input-list-facebook").bind( "DOMNodeRemoved DOMNodeInserted", function(){ 
            global_formNavigate = false; 
        });
    }
})(jQuery);;/*
* +--------------------------------------------------------------------+
* | CiviCRM version 3.1                                                |
* +--------------------------------------------------------------------+
* | Copyright CiviCRM LLC (c) 2004-2010                                |
* +--------------------------------------------------------------------+
* | This file is a part of CiviCRM.                                    |
* |                                                                    |
* | CiviCRM is free software; you can copy, modify, and distribute it  |
* | under the terms of the GNU Affero General Public License           |
* | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
* |                                                                    |
* | CiviCRM is distributed in the hope that it will be useful, but     |
* | WITHOUT ANY WARRANTY; without even the implied warranty of         |
* | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
* | See the GNU Affero General Public License for more details.        |
* |                                                                    |
* | You should have received a copy of the GNU Affero General Public   |
* | License and the CiviCRM Licensing Exception along                  |
* | with this program; if not, contact CiviCRM LLC                     |
* | at info[AT]civicrm[DOT]org. If you have questions about the        |
* | GNU Affero General Public License or the licensing of CiviCRM,     |
* | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
* +--------------------------------------------------------------------+
*/ 
(function($){ $.fn.crmaccordions = function(){
	if ($('.crm-accordion-processed').length == 0){
	$('.crm-accordion-header').live('mouseover',
		function() {$(this).addClass('crm-accordion-header-hover')});
	$('.crm-accordion-header').live('mouseout',	
		function() {$(this).removeClass('crm-accordion-header-hover')});
	$('.crm-accordion-header').live('click', function () {
		$(this).parent().toggleClass('crm-accordion-open');
		$(this).parent().toggleClass('crm-accordion-closed');
		});
	$('.crm-accordion-wrapper').addClass('crm-accordion-processed'); // only attached to accordions processed during first run
	};
};
})(jQuery);
;/*
 * Alternate Select Multiple (crmasmSelect) 1.0.4 beta - jQuery Plugin
 * http://www.ryancramer.com/projects/crmasmSelect/
 * 
 * Copyright (c) 2008 by Ryan Cramer - http://www.ryancramer.com
 * 
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 */

(function($) {

	$.fn.crmasmSelect = function(customOptions) {

		var options = {

			listType: 'ul',						// Ordered list 'ol', or unordered list 'ul'
			sortable: false, 					// Should the list be sortable?
			highlight: false,					// Use the highlight feature? 
			animate: false,						// Animate the the adding/removing of items in the list?
			addItemTarget: 'bottom',				// Where to place new selected items in list: top or bottom
			hideWhenAdded: false,					// Hide the option when added to the list? works only in FF
			debugMode: false,					// Debug mode keeps original select visible 

			removeLabel: 'X',					// Text used in the "remove" link
			highlightAddedLabel: 'Added: ',				// Text that precedes highlight of added item
			highlightRemovedLabel: 'Removed: ',			// Text that precedes highlight of removed item

			containerClass: 'crmasmContainer',				// Class for container that wraps this widget
			selectClass: 'crmasmSelect',				// Class for the newly created <select>
			optionDisabledClass: 'asmOptionDisabled',		// Class for items that are already selected / disabled
			listClass: 'crmasmList',					// Class for the list ($ol)
			listSortableClass: 'crmasmListSortable',			// Another class given to the list when it is sortable
			listItemClass: 'crmasmListItem',				// Class for the <li> list items
			listItemLabelClass: 'crmasmListItemLabel',			// Class for the label text that appears in list items
			removeClass: 'crmasmListItemRemove',			// Class given to the "remove" link
			highlightClass: 'crm asmHighlight',				// Class given to the highlight <span>

			respectParents: false				// Force selection of "parent" option items
			};

		$.extend(options, customOptions); 

		return this.each(function(index) {

			var $original = $(this); 				// the original select multiple
			var $container; 					// a container that is wrapped around our widget
			var $select; 						// the new select we have created
			var $ol; 						// the list that we are manipulating
			var buildingSelect = false; 				// is the new select being constructed right now?
			var ieClick = false;					// in IE, has a click event occurred? ignore if not
			var ignoreOriginalChangeEvent = false;			// originalChangeEvent bypassed when this is true

			function init() {

				// initialize the alternate select multiple

				// this loop ensures uniqueness, in case of existing crmasmSelects placed by ajax (1.0.3)
				while($("#" + options.containerClass + index).size() > 0) index++; 

				$select = $("<select></select>")
					.addClass(options.selectClass)
					.attr('id', options.selectClass + index); 

				$selectRemoved = $("<select></select>"); 

				$ol = $("<" + options.listType + "></" + options.listType + ">")
					.addClass(options.listClass)
					.attr('id', options.listClass + index); 

				$container = $("<div></div>")
					.addClass(options.containerClass) 
					.attr('id', options.containerClass + index); 

				buildSelect();

				$select.change(selectChangeEvent)
					.click(selectClickEvent); 

				$original.change(originalChangeEvent)
					.wrap($container).before($select).before($ol);

				if(options.sortable) makeSortable();

				if($.browser.msie) $ol.css('display', 'inline-block'); 
			}

			function makeSortable() {

				// make any items in the selected list sortable
				// requires jQuery UI sortables, draggables, droppables

				$ol.sortable({
					items: 'li.' + options.listItemClass,
					handle: '.' + options.listItemLabelClass,
					axis: 'y',
					update: function(e, data) {

						var updatedOptionId;

						$(this).children("li").each(function(n) {

							$option = $('#' + $(this).attr('rel')); 

							if($(this).is(".ui-sortable-helper")) {
								updatedOptionId = $option.attr('id'); 
								return;
							}

							$original.append($option); 
						}); 

						if(updatedOptionId) triggerOriginalChange(updatedOptionId, 'sort'); 
					}

				}).addClass(options.listSortableClass); 
			}

			function selectChangeEvent(e) {
				
				// an item has been selected on the regular select we created
				// check to make sure it's not an IE screwup, and add it to the list

				if($.browser.msie && $.browser.version < 7 && !ieClick) return;
				var id = $(this).children("option:selected").slice(0,1).attr('rel'); 
				addListItem(id); 	
				ieClick = false; 
				triggerOriginalChange(id, 'add'); // for use by user-defined callbacks
			}

			function selectClickEvent() {

				// IE6 lets you scroll around in a select without it being pulled down
				// making sure a click preceded the change() event reduces the chance
				// if unintended items being added. there may be a better solution?

				ieClick = true; 
			}

			function originalChangeEvent(e) {

				// select or option change event manually triggered
				// on the original <select multiple>, so rebuild ours

				if(ignoreOriginalChangeEvent) {
					ignoreOriginalChangeEvent = false; 
					return; 
				}

				$select.empty();
				$ol.empty();
				buildSelect();

				// opera has an issue where it needs a force redraw, otherwise
				// the items won't appear until something else forces a redraw
				if($.browser.opera) $ol.hide().fadeIn("fast");
			}

			function buildSelect() {

				// build or rebuild the new select that the user
				// will select items from

				buildingSelect = true; 

				// add a first option to be the home option / default selectLabel
				$select.prepend("<option>" + $original.attr('title') + "</option>"); 

				$original.children("option").each(function(n) {

					var $t = $(this); 
					var id; 

					if(!$t.attr('id')) $t.attr('id', 'asm' + index + 'option' + n); 
					id = $t.attr('id');
					classes = $t.attr('class'); 

					if($t.is(":selected")) {
						addListItem(id); 
						addSelectOption(id, classes, true); 						
					} else {
						addSelectOption(id,classes); 
					}
				});

				if(!options.debugMode) $original.hide(); // IE6 requires this on every buildSelect()
				selectFirstItem();
				buildingSelect = false; 
			}

			function addSelectOption(optionId, optionClasses, disabled) {

				// add an <option> to the <select>
				// used only by buildSelect()

				if(disabled == undefined) var disabled = false; 

				var $O = $('#' + optionId); 
				var $option = $("<option>" + $O.text() + "</option>")
					.val($O.val())
					.attr('rel', optionId)
					.addClass(optionClasses);

				if(disabled) disableSelectOption($option); 

				$select.append($option); 
			}

			function selectFirstItem() {

				// select the firm item from the regular select that we created

				$select.children(":eq(0)").attr("selected", true); 
			}

			function disableSelectOption($option) {

				// make an option disabled, indicating that it's already been selected
				// because safari is the only browser that makes disabled items look 'disabled'
				// we apply a class that reproduces the disabled look in other browsers

				$option.addClass(options.optionDisabledClass)
					.attr("selected", false)
					.attr("disabled", true);

				if(options.hideWhenAdded) $option.hide();
				if($.browser.msie) $select.hide().show(); // this forces IE to update display
			}

			function enableSelectOption($option) {

				// given an already disabled select option, enable it

				$option.removeClass(options.optionDisabledClass)
					.attr("disabled", false);

				if(options.hideWhenAdded) $option.show();
				if($.browser.msie) $select.hide().show(); // this forces IE to update display
			}

			function addListItem(optionId) {

				// add a new item to the html list

				var $O = $('#' + optionId); 

				if(!$O) return; // this is the first item, selectLabel

				var $removeLink = $("<a></a>")
					.attr("href", "#")
					.addClass(options.removeClass)
					.prepend(options.removeLabel)
					.click(function() { 
						dropListItem($(this).parent('li').attr('rel')); 
						return false; 
					}); 

				var $itemLabel = $("<span></span>")
					.addClass(options.listItemLabelClass)
					.html($O.html()); 

				var $item = $("<li></li>")
					.attr('rel', optionId)
					.addClass(options.listItemClass)
					.addClass($O.attr('class'))
					.append($itemLabel)
					.append($removeLink)
					.hide();

				if(!buildingSelect) {
					if($O.is(":selected")) return; // already have it
					$O.attr('selected', true); 
				}

				if(options.addItemTarget == 'top' && !buildingSelect) {
					$ol.prepend($item); 
					if(options.sortable) $original.prepend($O); 
				} else {
					$ol.append($item); 
					if(options.sortable) $original.append($O); 
				}

				addListItemShow($item); 

				disableSelectOption($("[rel=" + optionId + "]", $select));

				if(!buildingSelect) {
					setHighlight($item, options.highlightAddedLabel); 
					selectFirstItem();
					if(options.sortable) $ol.sortable("refresh"); 	
				}
				
				if(options.respectParents) {
				
				if($O.hasClass('child')) {
					parentName = $O.attr('class').split('parent-')[1];
					parentName = parentName.split(' ')[0];
					parentId = $('.option-'+parentName).attr('rel');
					addListItem(parentId);
					};
				};
			}

			function addListItemShow($item) {

				// reveal the currently hidden item with optional animation
				// used only by addListItem()

				if(options.animate && !buildingSelect) {
					$item.animate({
						opacity: "show",
						height: "show"
					}, 100, "swing", function() { 
						$item.animate({
							height: "+=2px"
						}, 50, "swing", function() {
							$item.animate({
								height: "-=2px"
							}, 25, "swing"); 
						}); 
					}); 
				} else {
					$item.show();
				}
			}

			function dropListItem(optionId, highlightItem) {

				// remove an item from the html list

				if(highlightItem == undefined) var highlightItem = true; 
				var $O = $('#' + optionId); 

				$O.attr('selected', false); 
				$item = $ol.children("li[rel=" + optionId + "]");

				dropListItemHide($item); 
				enableSelectOption($("[rel=" + optionId + "]", options.removeWhenAdded ? $selectRemoved : $select));

				if(highlightItem) setHighlight($item, options.highlightRemovedLabel); 

				triggerOriginalChange(optionId, 'drop'); 
				
			}

			function dropListItemHide($item) {

				// remove the currently visible item with optional animation
				// used only by dropListItem()

				if(options.animate && !buildingSelect) {

					$prevItem = $item.prev("li");

					$item.animate({
						opacity: "hide",
						height: "hide"
					}, 100, "linear", function() {
						$prevItem.animate({
							height: "-=2px"
						}, 50, "swing", function() {
							$prevItem.animate({
								height: "+=2px"
							}, 100, "swing"); 
						}); 
						$item.remove(); 
					}); 
					
				} else {
					$item.remove(); 
				}
			}

			function setHighlight($item, label) {

				// set the contents of the highlight area that appears
				// directly after the <select> single
				// fade it in quickly, then fade it out

				if(!options.highlight) return; 

				$select.next("#" + options.highlightClass + index).remove();

				var $highlight = $("<span></span>")
					.hide()
					.addClass(options.highlightClass)
					.attr('id', options.highlightClass + index)
					.html(label + $item.children("." + options.listItemLabelClass).slice(0,1).text()); 
					
				$select.after($highlight); 

				$highlight.fadeIn("fast", function() {
					setTimeout(function() { $highlight.fadeOut("slow"); }, 50); 
				}); 
			}

			function triggerOriginalChange(optionId, type) {

				// trigger a change event on the original select multiple
				// so that other scripts can pick them up

				ignoreOriginalChangeEvent = true; 
				$option = $("#" + optionId); 

				$original.trigger('change', [{
					'option': $option,
					'value': $option.val(),
					'id': optionId,
					'item': $ol.children("[rel=" + optionId + "]"),
					'type': type
				}]); 
			}

			init();
		});
	};

})(jQuery); 

 ;/*
* +--------------------------------------------------------------------+
* | CiviCRM version 3.1                                                |
* +--------------------------------------------------------------------+
* | Copyright CiviCRM LLC (c) 2004-2010                                |
* +--------------------------------------------------------------------+
* | This file is a part of CiviCRM.                                    |
* |                                                                    |
* | CiviCRM is free software; you can copy, modify, and distribute it  |
* | under the terms of the GNU Affero General Public License           |
* | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
* |                                                                    |
* | CiviCRM is distributed in the hope that it will be useful, but     |
* | WITHOUT ANY WARRANTY; without even the implied warranty of         |
* | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
* | See the GNU Affero General Public License for more details.        |
* |                                                                    |
* | You should have received a copy of the GNU Affero General Public   |
* | License and the CiviCRM Licensing Exception along                  |
* | with this program; if not, contact CiviCRM LLC                     |
* | at info[AT]civicrm[DOT]org. If you have questions about the        |
* | GNU Affero General Public License or the licensing of CiviCRM,     |
* | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
* +--------------------------------------------------------------------+
*/ 
(function($){ $.fn.crmtooltip = function(){
	$('a.crm-summary-link')
	.addClass('crm-processed')
	.hover(
		function()  {
			if ($(this).children('.crm-tooltip-wrapper').length == '') {
				$(this).append('<div class="crm-tooltip-wrapper"><div class="crm-tooltip"></div></div>');
				$(this).children().children('.crm-tooltip')
					.html('<div class="crm-loading-element"></div>')
					.load(this.href);
			} else {
				$(this).children('.crm-tooltip-wrapper').show();
			}
		},
		function(){$('.crm-tooltip-wrapper').hide()}
		)
	.click(function() {return false});	
	
	};
	})(jQuery);

;/**
* DD_roundies, this adds rounded-corner CSS in standard browsers and VML sublayers in IE that accomplish a similar appearance when comparing said browsers.
* Author: Drew Diller
* Email: drew.diller@gmail.com
* URL: http://www.dillerdesign.com/experiment/DD_roundies/
* Version: 0.0.2a -  preview 2008.12.26
* Licensed under the MIT License: http://dillerdesign.com/experiment/DD_roundies/#license
*
* Usage:
* DD_roundies.addRule('#doc .container', '10px 5px'); // selector and multiple radii
* DD_roundies.addRule('.box', 5, true); // selector, radius, and optional addition of border-radius code for standard browsers.
* 
* Just want the PNG fixing effect for IE6, and don't want to also use the DD_belatedPNG library?  Don't give any additional arguments after the CSS selector.
* DD_roundies.addRule('.your .example img');
**/

eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('t K={16:\'K\',1L:G,1M:G,1d:G,2f:y(){u(D.2g!=8&&D.1N&&!D.1N[q.16]){q.1L=M;q.1M=M}17 u(D.2g==8){q.1d=M}},2h:D.2i,1O:[],1b:{},2j:y(){u(q.1L||q.1M){D.1N.2L(q.16,\'2M:2N-2O-2P:x\')}u(q.1d){D.2Q(\'<?2R 2S="\'+q.16+\'" 2T="#1P#2k" ?>\')}},2l:y(){t a=D.1k(\'z\');D.2m.1w.1Q(a,D.2m.1w.1w);u(a.12){2n{t b=a.12;b.1x(q.16+\'\\\\:*\',\'{1l:2U(#1P#2k)}\');q.12=b}2o(2p){}}17{q.12=a}},1x:y(a,b,c){u(1R b==\'1S\'||b===2V){b=0}u(b.2W.2q().1y(\'2X\')==-1){b=b.2q().2Y(/[^0-9 ]/g,\'\').1T(\' \')}H(t i=0;i<4;i++){b[i]=(!b[i]&&b[i]!==0)?b[C.1e((i-2),0)]:b[i]}u(q.12){u(q.12.1x){t d=a.1T(\',\');H(t i=0;i<d.1U;i++){q.12.1x(d[i],\'1l:2Z(K.1V.2r(q, [\'+b.1W(\',\')+\']))\')}}17 u(c){t e=b.1W(\'F \')+\'F\';q.12.1z(D.2s(a+\' {Q-1f:\'+e+\'; -30-Q-1f:\'+e+\';}\'));q.12.1z(D.2s(a+\' {-1A-Q-1m-1n-1f:\'+b[0]+\'F \'+b[0]+\'F; -1A-Q-1m-1X-1f:\'+b[1]+\'F \'+b[1]+\'F; -1A-Q-1Y-1X-1f:\'+b[2]+\'F \'+b[2]+\'F; -1A-Q-1Y-1n-1f:\'+b[3]+\'F \'+b[3]+\'F;}\'))}}17 u(q.1d){q.1O.31({\'2t\':a,\'2u\':b})}},2v:y(a){2w(32.33){I\'z.Q\':I\'z.34\':I\'z.1B\':q.1o(a);13;I\'z.2x\':q.1Z(a);13;I\'z.1p\':I\'z.2y\':I\'z.2z\':q.1o(a);13;I\'z.20\':a.18.z.20=(a.z.20==\'S\')?\'S\':\'35\';13;I\'z.21\':q.22(a);13;I\'z.1c\':a.18.z.1c=a.z.1c;13}},1o:y(a){a.14.23=\'\';q.2A(a);q.1Z(a);q.1C(a);q.1D(a);q.24(a);q.2B(a);q.22(a)},22:y(a){u(a.W.21.1y(\'36\')!=-1){t b=a.W.21;b=1g(b.37(b.25(\'=\')+1,b.25(\')\')),10)/2C;H(t v 1h a.x){a.x[v].1i.38=b}}},2A:y(a){u(!a.W){1q}17{t b=a.W}a.14.1p=\'\';a.14.1E=\'\';t c=(b.1p==\'2D\');t d=M;u(b.1E!=\'S\'||a.1F){u(!a.1F){a.J=b.1E;a.J=a.J.39(5,a.J.25(\'")\')-5)}17{a.J=a.26}t e=q;u(!e.1b[a.J]){t f=D.1k(\'3a\');f.1r(\'3b\',y(){q.1s=q.3c;q.1t=q.3d;e.1D(a)});f.3e=e.16+\'3f\';f.14.23=\'1l:S; 1j:27; 1m:-2E; 1n:-2E; Q:S;\';f.26=a.J;f.2F(\'1s\');f.2F(\'1t\');D.2G.1Q(f,D.2G.1w);e.1b[a.J]=f}a.x.Z.1i.26=a.J;d=G}a.x.Z.2H=!d;a.x.Z.1G=\'S\';a.x.1u.2H=!c;a.x.1u.1G=b.1p;a.14.1E=\'S\';a.14.1p=\'2D\'},1Z:y(a){a.x.1H.1G=a.W.2x},1C:y(a){t c=[\'N\',\'19\',\'1a\',\'O\'];a.P={};H(t b=0;b<4;b++){a.P[c[b]]=1g(a.W[\'Q\'+c[b]+\'U\'],10)||0}},1D:y(c){t e=[\'O\',\'N\',\'U\',\'V\'];H(t d=0;d<4;d++){c.E[e[d]]=c[\'3g\'+e[d]]}t f=y(a,b){a.z.1n=(b?0:c.E.O)+\'F\';a.z.1m=(b?0:c.E.N)+\'F\';a.z.1s=c.E.U+\'F\';a.z.1t=c.E.V+\'F\'};H(t v 1h c.x){t g=(v==\'Z\')?1:2;c.x[v].3h=(c.E.U*g)+\', \'+(c.E.V*g);f(c.x[v],M)}f(c.18,G);u(K.1d){c.x.1H.z.28=\'-3i\';u(1R c.P==\'1S\'){q.1C(c)}c.x.1u.z.28=(c.P.N-1)+\'F \'+(c.P.O-1)+\'F\'}},24:y(j){t k=y(a,w,h,r,b,c,d){t e=a?[\'m\',\'1I\',\'l\',\'1J\',\'l\',\'1I\',\'l\',\'1J\',\'l\']:[\'1J\',\'l\',\'1I\',\'l\',\'1J\',\'l\',\'1I\',\'l\',\'m\'];b*=d;c*=d;w*=d;h*=d;t R=r.2I();H(t i=0;i<4;i++){R[i]*=d;R[i]=C.3j(w/2,h/2,R[i])}t f=[e[0]+C.11(0+b)+\',\'+C.11(R[0]+c),e[1]+C.11(R[0]+b)+\',\'+C.11(0+c),e[2]+C.15(w-R[1]+b)+\',\'+C.11(0+c),e[3]+C.15(w+b)+\',\'+C.11(R[1]+c),e[4]+C.15(w+b)+\',\'+C.15(h-R[2]+c),e[5]+C.15(w-R[2]+b)+\',\'+C.15(h+c),e[6]+C.11(R[3]+b)+\',\'+C.15(h+c),e[7]+C.11(0+b)+\',\'+C.15(h-R[3]+c),e[8]+C.11(0+b)+\',\'+C.11(R[0]+c)];u(!a){f.3k()}t g=f.1W(\'\');1q g};u(1R j.P==\'1S\'){q.1C(j)}t l=j.P;t m=j.2J.2I();t n=k(M,j.E.U,j.E.V,m,0,0,2);m[0]-=C.1e(l.O,l.N);m[1]-=C.1e(l.N,l.19);m[2]-=C.1e(l.19,l.1a);m[3]-=C.1e(l.1a,l.O);H(t i=0;i<4;i++){m[i]=C.1e(m[i],0)}t o=k(G,j.E.U-l.O-l.19,j.E.V-l.N-l.1a,m,l.O,l.N,2);t p=k(M,j.E.U-l.O-l.19+1,j.E.V-l.N-l.1a+1,m,l.O,l.N,1);j.x.1u.29=o;j.x.Z.29=p;j.x.1H.29=n+o;q.2K(j)},2B:y(a){t s=a.W;t b=[\'N\',\'O\',\'19\',\'1a\'];H(t i=0;i<4;i++){a.14[\'1B\'+b[i]]=(1g(s[\'1B\'+b[i]],10)||0)+(1g(s[\'Q\'+b[i]+\'U\'],10)||0)+\'F\'}a.14.Q=\'S\'},2K:y(e){t f=K;u(!e.J||!f.1b[e.J]){1q}t g=e.W;t h={\'X\':0,\'Y\':0};t i=y(a,b){t c=M;2w(b){I\'1n\':I\'1m\':h[a]=0;13;I\'3l\':h[a]=0.5;13;I\'1X\':I\'1Y\':h[a]=1;13;1P:u(b.1y(\'%\')!=-1){h[a]=1g(b,10)*0.3m}17{c=G}}t d=(a==\'X\');h[a]=C.15(c?((e.E[d?\'U\':\'V\']-(e.P[d?\'O\':\'N\']+e.P[d?\'19\':\'1a\']))*h[a])-(f.1b[e.J][d?\'1s\':\'1t\']*h[a]):1g(b,10));h[a]+=1};H(t b 1h h){i(b,g[\'2y\'+b])}e.x.Z.1i.1j=(h.X/(e.E.U-e.P.O-e.P.19+1))+\',\'+(h.Y/(e.E.V-e.P.N-e.P.1a+1));t j=g.2z;t c={\'T\':1,\'R\':e.E.U+1,\'B\':e.E.V+1,\'L\':1};t k={\'X\':{\'2a\':\'L\',\'2b\':\'R\',\'d\':\'U\'},\'Y\':{\'2a\':\'T\',\'2b\':\'B\',\'d\':\'V\'}};u(j!=\'2c\'){c={\'T\':(h.Y),\'R\':(h.X+f.1b[e.J].1s),\'B\':(h.Y+f.1b[e.J].1t),\'L\':(h.X)};u(j.1y(\'2c-\')!=-1){t v=j.1T(\'2c-\')[1].3n();c[k[v].2a]=1;c[k[v].2b]=e.E[k[v].d]+1}u(c.B>e.E.V){c.B=e.E.V+1}}e.x.Z.z.3o=\'3p(\'+c.T+\'F \'+c.R+\'F \'+c.B+\'F \'+c.L+\'F)\'},1v:y(a){t b=q;2d(y(){b.1o(a)},1)},2e:y(a){q.1D(a);q.24(a)},1V:y(b){q.z.1l=\'S\';u(!q.W){1q}17{t c=q.W}t d={3q:G,3r:G,3s:G,3t:G,3u:G,3v:G,3w:G};u(d[q.1K]===G){1q}t e=q;t f=K;q.2J=b;q.E={};t g={3x:\'2e\',3y:\'2e\'};u(q.1K==\'A\'){t i={3z:\'1v\',3A:\'1v\',3B:\'1v\',3C:\'1v\'};H(t a 1h i){g[a]=i[a]}}H(t h 1h g){q.1r(\'3D\'+h,y(){f[g[h]](e)})}q.1r(\'3E\',y(){f.2v(e)});t j=y(a){a.z.3F=1;u(a.W.1j==\'3G\'){a.z.1j=\'3H\'}};j(q.3I);j(q);q.18=D.1k(\'3J\');q.18.14.23=\'1l:S; 1j:27; 28:0; 1B:0; Q:0; 3K:S;\';q.18.z.1c=c.1c;q.x={\'1u\':M,\'Z\':M,\'1H\':M};H(t v 1h q.x){q.x[v]=D.1k(f.16+\':3L\');q.x[v].1i=D.1k(f.16+\':3M\');q.x[v].1z(q.x[v].1i);q.x[v].3N=G;q.x[v].z.1j=\'27\';q.x[v].z.1c=c.1c;q.x[v].3O=\'1,1\';q.18.1z(q.x[v])}q.x.Z.1G=\'S\';q.x.Z.1i.3P=\'3Q\';q.3R.1Q(q.18,q);q.1F=G;u(q.1K==\'3S\'){q.1F=M;q.z.3T=\'3U\'}2d(y(){f.1o(e)},1)}};2n{D.3V("3W",G,M)}2o(2p){}K.2f();K.2j();K.2l();u(K.1d&&D.1r&&K.2h){D.1r(\'3X\',y(){u(D.3Y==\'3Z\'){t d=K.1O;t e=d.1U;t f=y(a,b,c){2d(y(){K.1V.2r(a,b)},c*2C)};H(t i=0;i<e;i++){t g=D.2i(d[i].2t);t h=g.1U;H(t r=0;r<h;r++){u(g[r].1K!=\'40\'){f(g[r],d[i].2u,r)}}}}})}',62,249,'||||||||||||||||||||||||||this|||var|if|||vml|function|style|||Math|document|dim|px|false|for|case|vmlBg|DD_roundies||true|Top|Left|bW|border||none||Width|Height|currentStyle|||image||floor|styleSheet|break|runtimeStyle|ceil|ns|else|vmlBox|Right|Bottom|imgSize|zIndex|IE8|max|radius|parseInt|in|filler|position|createElement|behavior|top|left|applyVML|backgroundColor|return|attachEvent|width|height|color|pseudoClass|firstChild|addRule|search|appendChild|webkit|padding|vmlStrokeWeight|vmlOffsets|backgroundImage|isImg|fillcolor|stroke|qy|qx|nodeName|IE6|IE7|namespaces|selectorsToProcess|default|insertBefore|typeof|undefined|split|length|roundify|join|right|bottom|vmlStrokeColor|display|filter|vmlOpacity|cssText|vmlPath|lastIndexOf|src|absolute|margin|path|b1|b2|repeat|setTimeout|reposition|IEversion|documentMode|querySelector|querySelectorAll|createVmlNameSpace|VML|createVmlStyleSheet|documentElement|try|catch|err|toString|call|createTextNode|selector|radii|readPropertyChanges|switch|borderColor|backgroundPosition|backgroundRepeat|vmlFill|nixBorder|100|transparent|10000px|removeAttribute|body|filled|slice|DD_radii|clipImage|add|urn|schemas|microsoft|com|writeln|import|namespace|implementation|url|null|constructor|Array|replace|expression|moz|push|event|propertyName|borderWidth|block|lpha|substring|opacity|substr|img|onload|offsetWidth|offsetHeight|className|_sizeFinder|offset|coordsize|1px|min|reverse|center|01|toUpperCase|clip|rect|BODY|TABLE|TR|TD|SELECT|OPTION|TEXTAREA|resize|move|mouseleave|mouseenter|focus|blur|on|onpropertychange|zoom|static|relative|offsetParent|ignore|background|shape|fill|stroked|coordorigin|type|tile|parentNode|IMG|visibility|hidden|execCommand|BackgroundImageCache|onreadystatechange|readyState|complete|INPUT'.split('|'),0,{}))



$(document).ready(function() { 
$.uicornerfix = function(r){
  DD_roundies.addRule('.ui-corner-all', r);
  DD_roundies.addRule('.ui-corner-top', r+' '+r+' 0 0');
  DD_roundies.addRule('.ui-corner-bottom', '0 0 '+r+' '+r);
  DD_roundies.addRule('.ui-corner-right', '0 '+r+' '+r+' 0');
  DD_roundies.addRule('.ui-corner-left', r+' 0 0 '+r);
  DD_roundies.addRule('.ui-corner-tl', r+' 0 0 0');
  DD_roundies.addRule('.ui-corner-tr', '0 '+r+' 0 0');
  DD_roundies.addRule('.ui-corner-br', '0 0 '+r+' 0');
  DD_roundies.addRule('.ui-corner-bl', '0 0 0 '+r);
};
$.uicornerfix('4px');

DD_roundies.addRule('div.m ul#civicrm-menu, #crm-container #restmsg, #crm-container #help,  #crm-container .help, #crm-container .ui-tabs-panel, #crm-container .crm-tab-button, #crm-container .crm-content-block, #full-screen-header, .crm-tooltip, #crm-container .crm-pager, #crm-container form .section-hidden-border, #crm-container #search-status, #crm-container .crm-form-block, #crm-container .crm-search-tasks, #crm-container .crm-tasks, #crm-container div.form-item, #crm-container div.messages, div#crm-toolTip', '4px');
});
;DD_roundies.addRule('#crm-container .crm-form-block, #crm-container .crm-search-tasks, #crm-container div.form-item, #crm-container div.messages, div#crm-toolTip', '4px');


;/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

/** 
 *  This function can be used to clear default 'suggestive text' from an input field
 *  When the cursor is moved into the field.
 *  
 *  It is generally invoked by the input field's onFocus event. Use the reserved
 *  word 'this' to pass this object. EX: onFocus="clearFldVal(this);"
 * 
 * @access public
 * @param  fld The form field object whose value is to be cleared
 * @param  hideBlocks Array of element Id's to be hidden
 * @return none 
 */
function clearFldVal(fld) {
    if (fld.value == fld.defaultValue) {
        fld.value = "";
    }
}

/** 
 *  This function is called by default at the bottom of template files which have forms that have
 *  conditionally displayed/hidden sections and elements. The PHP is responsible for generating
 *  a list of 'blocks to show' and 'blocks to hide' and the template passes these parameters to
 *  this function.
 * 
 * @access public
 * @param  showBlocks Array of element Id's to be displayed
 * @param  hideBlocks Array of element Id's to be hidden
 * @param elementType Value to set display style to for showBlocks (e.g. 'block' or 'table-row' or ...)
 * @return none 
 */
function on_load_init_blocks(showBlocks, hideBlocks, elementType)
{   
    if ( elementType == null ) {
        var elementType = 'block';
    }
    
    /* This loop is used to display the blocks whose IDs are present within the showBlocks array */ 
    for ( var i = 0; i < showBlocks.length; i++ ) {
        var myElement = document.getElementById(showBlocks[i]);
        /* getElementById returns null if element id doesn't exist in the document */
        if (myElement != null) {
            myElement.style.display = elementType;
        } else {
	  alert('showBlocks array item not in .tpl = ' + showBlocks[i]);
        }
    }
    
    /* This loop is used to hide the blocks whose IDs are present within the hideBlocks array */ 
    for ( var i = 0; i < hideBlocks.length; i++ ) { 
        var myElement = document.getElementById(hideBlocks[i]);
        /* getElementById returns null if element id doesn't exist in the document */
        if (myElement != null) {
            myElement.style.display = 'none';
        } else {
	  alert('showBlocks array item not in .tpl = ' + hideBlocks[i]);
        }
    }
    
}

/** 
 *  This function is called when we need to show or hide a related form element (target_element)
 *  based on the value (trigger_value) of another form field (trigger_field).
 * 
 * @access public
 * @param  trigger_field_id     HTML id of field whose onchange is the trigger
 * @param  trigger_value        List of integers - option value(s) which trigger show-element action for target_field
 * @param  target_element_id    HTML id of element to be shown or hidden
 * @param  target_element_type  Type of element to be shown or hidden ('block' or 'table-row')
 * @param  field_type           Type of element radio/select
 * @param  invert               Boolean - if true, we HIDE target on value match; if false, we SHOW target on value match
 * @return none 
*/
function showHideByValue(trigger_field_id, trigger_value, target_element_id, target_element_type, field_type, invert ) {
    if ( target_element_type == null ) {
        var target_element_type = 'block';
    } else if ( target_element_type == 'table-row' ) {
	var target_element_type = '';
    }
    
    if (field_type == 'select') {
        var trigger = trigger_value.split("|");
        var selectedOptionValue = document.getElementById(trigger_field_id).options[document.getElementById(trigger_field_id).selectedIndex].value;	
        
        var target = target_element_id.split("|");
        for(var j = 0; j < target.length; j++) {
            if ( invert ) {  
                show(target[j], target_element_type);
            } else {
                hide(target[j],target_element_type);
            }
            for(var i = 0; i < trigger.length; i++) {
                if (selectedOptionValue == trigger[i]) {
                    if ( invert ) {  
                        hide(target[j],target_element_type);
                    } else {
                        show(target[j],target_element_type);
                    }	
                }
            }
        }
 
    } else if (field_type == 'radio') {

        var target = target_element_id.split("|");
        for(var j = 0; j < target.length; j++) {
	    if (document.getElementsByName(trigger_field_id)[0].checked) {
		if ( invert ) {  
		    hide(target[j], target_element_type);
		} else {
		    show(target[j], target_element_type);
		 }
	    } else {
		if ( invert ) {  
		    show(target[j], target_element_type);
		} else {
		    hide(target[j], target_element_type);
		}
	    }
	}
    }
}

/** 
 *  This function is called when we need to enable or disable a related form element (target_element)
 *  based on the value (trigger_value) of another form field (trigger_field).
 * 
 * @access public
 * @param  trigger_field_id     HTML id of field whose onchange is the trigger
 * @param  trigger_value        List of integers - option value(s) which trigger enable-element action for target_field
 * @param  target_element_id    HTML id of element to be enabled or disabled
 * @param  target_element_type  Type of element to be enabled or disabled ('block' or 'table-row')
 * @param  field_type           Type of element radio/select
 * @param  invert               Boolean - if true, we DISABLE target on value match; if false, we ENABLE target on value match
 * @return none 
*/
function enableDisableByValue(trigger_field_id, trigger_value, target_element_id, target_element_type, field_type, invert ) {
    if ( target_element_type == null ) {
        var target_element_type = 'block';
    } else if ( target_element_type == 'table-row' ) {
	var target_element_type = '';
    }
    
    if (field_type == 'select') {
        var trigger = trigger_value.split("|");
        var selectedOptionValue = document.getElementById(trigger_field_id).options[document.getElementById(trigger_field_id).selectedIndex].value;	
        
        var target = target_element_id.split("|");
        for(var j = 0; j < target.length; j++) {
  	    if (document.getElementById(target[j])) {
              if ( invert ) {  
                 document.getElementById(target[j]).disabled = false;
              } else {
                 document.getElementById(target[j]).disabled = true;
              }
	    }
            for(var i = 0; i < trigger.length; i++) {
                if (selectedOptionValue == trigger[i]) {
    	            if (document.getElementById(target[j])) {
                       if ( invert ) {  
			  document.getElementById(target[j]).disabled = true;
	               } else {
			  document.getElementById(target[j]).disabled = false;
	               }	
		    }
                }
            }
        }
 
    } else if (field_type == 'radio') {
        var target = target_element_id.split("|");
        for(var j = 0; j < target.length; j++) {
	    if (document.getElementsByName(trigger_field_id)[0].checked) {
	       if (document.getElementById(target[j])) {
		   if ( invert ) {  
			document.getElementById(target[j]).disabled = true;
		   } else {
			document.getElementById(target[j]).disabled = false;
		   }
		}
	    } else {
	       if (document.getElementById(target[j])) {
		   if ( invert ) {  
			document.getElementById(target[j]).disabled = false;
 		   } else {
			document.getElementById(target[j]).disabled = true;
	    	   }
		}
	    }
	}
    }
}

/** 
 *  This function is called when we need to Reset a related form element (target_element)
 *  based on the value (trigger_value) of another form field (trigger_field).
 * 
 * @access public
 * @param  trigger_field_id     HTML id of field whose onchange is the trigger
 * @param  trigger_value        List of integers - option value(s) which trigger reset action for target_field
 * @param  target_element_id    HTML id of element to be reset
 * @param  target_field_type    Field-Type of element to be reset ('radio' or 'text')
 * @param  field_type           Type of element radio/select
 * @param  invert               Boolean - if true, we RESET target on value-match; if false, we RESET target on No-value-match
 * @return none 
*/
function resetByValue(trigger_field_id, trigger_value, target_element_id, target_field_type, field_type, invert) {
    
    if (field_type == 'select') {
        var trigger = trigger_value.split("|");
        var selectedOptionValue = document.getElementById(trigger_field_id).options[document.getElementById(trigger_field_id).selectedIndex].value;	
        
        var target = target_element_id.split("|");
        for(var j = 0; j < target.length; j++) {
            for(var i = 0; i < trigger.length; i++) {
		if ( invert ) {
                  if (selectedOptionValue == trigger[i]) {
		       if (target_field_type == 'radio') {	
			   if (document.getElementsByName(target[j])) {
				for (var i=0; i<document.getElementsByName(target[j]).length; i++) {
				   if (document.getElementsByName(target[j])[i].checked) {
 				       document.getElementsByName(target[j])[i].checked = null;
				   }
				}
			   }
		       } else {	
	    	           if (document.getElementById(target[j])) {
			       document.getElementById(target[j]).value = "";
			   }
		       }
                  }
		} else {
		    if (selectedOptionValue != trigger[i]) {
		       if (target_field_type == 'radio') {	
			   if (document.getElementsByName(target[j])) {
				for (var i=0; i<document.getElementsByName(target[j]).length; i++) {
				   if (document.getElementsByName(target[j])[i].checked) {
 				       document.getElementsByName(target[j])[i].checked = null;
				   }
				}
			   }
		       } else {	
	    	           if (document.getElementById(target[j])) {
			       document.getElementById(target[j]).value = "";
			   }
		       }
		    }
		}
            }
        }

     } else if (field_type == 'radio') {
        var target = target_element_id.split("|");
        for(var j = 0; j < target.length; j++) {
	      if ( invert ) {
		   if (document.getElementsByName(trigger_field_id)[0].checked) {
		       if (target_field_type == 'radio') {	
			   if (document.getElementsByName(target[j])) {
				for (var i=0; i<document.getElementsByName(target[j]).length; i++) {
				   if (document.getElementsByName(target[j])[i].checked) {
 				       document.getElementsByName(target[j])[i].checked = null;
				   }
				}
			   }
		       } else {	
			       if (document.getElementById(target[j])) {
		  		   document.getElementById(target[j]).value = "";
			      }
		       }
		   }
	      } else {
		   if (!document.getElementsByName(trigger_field_id)[0].checked) {
		       if (target_field_type == 'radio') {	
			   if (document.getElementsByName(target[j])) {
				for (var i=0; i<document.getElementsByName(target[j]).length; i++) {
				   if (document.getElementsByName(target[j])[i].checked) {
 				       document.getElementsByName(target[j])[i].checked = null;
				   }
				}
			   }
		       } else {	
			       if (document.getElementById(target[j])) {
		  		   document.getElementById(target[j]).value = "";
			      }
		       }
		   }
	      }
	}
    }
}

/** 
 * This function is used to display a page element  (e.g. block or table row or...). 
 * 
 * This function is called by various links which handle requests to display the hidden blocks.
 * An example is the <code>[+] another phone</code> link which expands an additional phone block.
 * The parameter block_id must have the id of the block which has to be displayed.
 *
 * 
 * @access public
 * @param block_id Id value of the block (or row) to be displayed.
 * @param elementType Value to set display style to when showing the element (e.g. 'block' or 'table-row' or ...)
 * @return none
 */
function show(block_id,elementType)
{
    if ( elementType == null ) {
        var elementType = 'block';
    } else if ( elementType == "table-row" && navigator.appName == 'Microsoft Internet Explorer' ) {
 	var elementType = "block";
    }
    var myElement = document.getElementById(block_id);
    if (myElement != null) {
        myElement.style.display = elementType;
    } else {
        alert('Request to show() function failed. Element id undefined = '+ block_id);
    }
}


/** 
 * This function is used to hide a block. 
 * 
 * This function is called by various links which handle requests to hide the visible blocks.
 * An example is the <code>[-] hide phone</code> link which hides the phone block.
 * The parameter block_id must have the id of the block which has to be hidden.
 *
 * @access public
 * @param block_id Id value of the block to be hidden.
 * @return none
 */
function hide(block_id) 
{
    var myElement = document.getElementById(block_id);
    if (myElement != null) {
        myElement.style.display = 'none';
    } else {
        alert('Request to hide() function failed. Element id undefined = ' + block_id);
    }
    
    //    document.getElementById(block_id).style.display = 'none';
}

/**
 *
 * Function for checking ALL or unchecking ALL check boxes in a resultset page.
 *
 * @access public
 * @param fldPrefix - common string which precedes unique checkbox ID and identifies field as
 *                    belonging to the resultset's checkbox collection
 * @param action - 'select' = set all to checked; 'deselect' = set all to unchecked
 * @param form - name of form that checkboxes are part of
 * Sample usage: onClick="javascript:changeCheckboxValues('chk_', 'select', myForm );"
 *
 * @return
 */
function toggleCheckboxVals(fldPrefix,object) {
    if ( object.id == 'toggleSelect' && cj(object).is(':checked') ) {
       cj( 'Input[id*="' + fldPrefix + '"],Input[id*="toggleSelect"]').attr('checked', true);
    } else {
       cj( 'Input[id*="' + fldPrefix + '"],Input[id*="toggleSelect"]').attr('checked', false);
    }
   /* function called to change the color of selected rows */
   on_load_init_checkboxes(object.form.name); 
}

function countSelectedCheckboxes(fldPrefix, form) {
    fieldCount = 0;
    for( i=0; i < form.elements.length; i++) {
        fpLen = fldPrefix.length;
        if (form.elements[i].type == 'checkbox' && form.elements[i].name.slice(0,fpLen) == fldPrefix && form.elements[i].checked == true) {
            fieldCount++;
        }
    }
    return fieldCount;
}

/**
 * Function to enable task action select
 */
function toggleTaskAction( status ) {
    var radio_ts = document.getElementsByName('radio_ts');
    if (!radio_ts[1]) {
	radio_ts[0].checked = true;
    }
    if ( radio_ts[0].checked || radio_ts[1].checked ) {
	status = true;
    }

    var formElements = ['task', 'Go', 'Print'];
    for(var i=0; i<formElements.length; i++ ) {
	var element = document.getElementById( formElements[i] );
	if ( element ) {
	    if ( status ) {
		element.disabled = false;
	    } else {
		element.disabled = true;
	    }
	}
    }
}

/**
 * This function is used to check if any actio is selected and also to check if any contacts are checked.
 *
 * @access public
 * @param fldPrefix - common string which precedes unique checkbox ID and identifies field as
 *                    belonging to the resultset's checkbox collection
 * @param form - name of form that checkboxes are part of
 * Sample usage: onClick="javascript:checkPerformAction('chk_', myForm );"
 *
 */
function checkPerformAction (fldPrefix, form, taskButton) {
    var cnt;
    var gotTask = 0;
    
    // taskButton TRUE means we don't need to check the 'task' field - it's a button-driven task
    if (taskButton == 1) {
        gotTask = 1;
    } else if (document.forms[form].task.selectedIndex) {
	//force user to select all search contacts, CRM-3711
	if ( document.forms[form].task.value == 13 || document.forms[form].task.value == 14 || document.forms[form].task.value == 20 ) {
	    var toggleSelect = document.getElementsByName('toggleSelect');
	    if ( toggleSelect[0].checked || document.forms[form].radio_ts[0].checked ) {
		return true;
	    } else {
		alert( "Please select all contacts for this action.\n\nTo use the entire set of search results, click the 'all records' radio button." );
		return false;
	    }
	}
	gotTask = 1; 
    }
    
    if (gotTask == 1) {
        // If user wants to perform action on ALL records and we have a task, return (no need to check further)
        if (document.forms[form].radio_ts[0].checked) {
            return true;
        }
	
        cnt = countSelectedCheckboxes(fldPrefix, document.forms[form]);
        if (!cnt) {
            alert ("Please select one or more contacts for this action.\n\nTo use the entire set of search results, click the 'all records' radio button.");
            return false;
        }
    } else {
        alert ("Please select an action from the drop-down menu.");
        return false;
    }
}

/**
 * This function changes the style for a checkbox block when it is selected.
 *
 * @access public
 * @param chkName - it is name of the checkbox
 * @param form - name of form that checkboxes are part of
 * @return null
 */
function checkSelectedBox (chkName, form) 
{
    var ss = document.forms[form].elements[chkName].name.substring(7,document.forms[form].elements[chkName].name.length);
    
    var row = 'rowid' + ss;
  
    if (document.forms[form].elements[chkName].checked == true) {
        // change 'all records' radio to 'selected' if any row is checked
        document.forms[form].radio_ts[1].checked = true;
        
        if (document.getElementById(row).className == 'even-row') {
            document.getElementById(row).className = 'row-selected even-row';
        } else {
            document.getElementById(row).className = 'row-selected odd-row';
        }
	
    } else {
        if (document.getElementById(row).className == 'row-selected even-row') {
            document.getElementById(row).className = 'even-row';
        } else if (document.getElementById(row).className == 'row-selected odd-row') {
            document.getElementById(row).className = 'odd-row';
        }
    }
}


/**
 * This function is to show the row with  selected checkbox in different color
 * @param form - name of form that checkboxes are part of
 *
 * @access public
 * @return null
 */

function on_load_init_checkboxes(form) 
{
    var formName = form;
    var fldPrefix = 'mark_x';
    for( i=0; i < document.forms[formName].elements.length; i++) {
	fpLen = fldPrefix.length;
	if (document.forms[formName].elements[i].type == 'checkbox' && document.forms[formName].elements[i].name.slice(0,fpLen) == fldPrefix ) {
	    checkSelectedBox (document.forms[formName].elements[i].name, formName); 
	}
    }
    
}

/**
 * Function to change the color of the class
 * 
 * @param form - name of the form
 * @param rowid - id of the <tr>, <div> you want to change
 *
 * @access public
 * @return null
 */

function changeRowColor (rowid, form) {

    switch (document.getElementById(rowid).className) 	{
    case 'even-row'          : 	document.getElementById(rowid).className = 'selected even-row';
	break;
    case 'odd-row'           : 	document.getElementById(rowid).className = 'selected odd-row';
	break;
    case 'selected even-row' : 	document.getElementById(rowid).className = 'even-row';
	break;
    case 'selected odd-row'  : 	document.getElementById(rowid).className = 'odd-row';
	break;
    case 'form-item'         : 	document.getElementById(rowid).className = 'selected';
	break;
    case 'selected'          : 	document.getElementById(rowid).className = 'form-item';
	
    }
}

/**
 * This function is to show the row with  selected checkbox in different color
 * @param form - name of form that checkboxes are part of
 *
 * @access public
 * @return null
 */

function on_load_init_check(form) 
{
    for( i=0; i < document.forms[form].elements.length; i++) {
	
      if (
          ( document.forms[form].elements[i].type == 'checkbox' && document.forms[form].elements[i].checked == true )
           ||
          ( document.forms[form].elements[i].type == 'hidden' && document.forms[form].elements[i].value == 1 )
         ) {
              var ss = document.forms[form].elements[i].id;
		      var row = 'rowid' + ss;
		      changeRowColor(row, form);
           }
    }
}

/**
 * reset all the radio buttons with a given name
 *
 * @param string fieldName
 * @param object form
 * @return null
 */
function unselectRadio(fieldName, form)
{
  for( i=0; i < document.forms[form].elements.length; i++) {
    if (document.forms[form].elements[i].name == fieldName) {
      document.forms[form].elements[i].checked = false;
    }
  }
  return;
}

/**
 * Function to change button text and disable one it is clicked
 *
 * @param obj object - the button clicked
 * @param formID string - the id of the form being submitted
 * @param string procText - button text after user clicks it
 * @return null
 */
var submitcount=0;
/* Changes button label on submit, and disables button after submit for newer browsers.
Puts up alert for older browsers. */
function submitOnce(obj,formId,procText) {
    // if named button clicked, change text
    if (obj.value != null) {
        obj.value = procText + " ...";
    }
    if (document.getElementById) { // disable submit button for newer browsers
        obj.disabled = true;
        document.getElementById(formId).submit();
        return true;
    }
    else { // for older browsers
        if (submitcount == 0) {
            submitcount++;
            return true;
        } else {
            alert("Your request is currently being processed ... Please wait.");
            return false;
        }
    }
}

/**
 * Function submits referenced form on click of wizard nav link.
 * Populates targetPage hidden field prior to POST.
 *
 * @param formID string - the id of the form being submitted
 * @param targetPage - identifier of wizard section target
 * @return null
 */
function submitCurrentForm(formId,targetPage) {
    alert(formId + ' ' + targetPage);
    document.getElementById(formId).targetPage.value = targetPage;
    document.getElementById(formId).submit();
}

/**
 * Function counts and controls maximum word count for textareas.
 *
 * @param essay_id string - the id of the essay (textarea) field
 * @param wc - int - number of words allowed
 * @return null
 */
function countit(essay_id,wc){
    var text_area       = document.getElementById("essay_" + essay_id);
    var count_element   = document.getElementById("word_count_" + essay_id);
    var count           = 0;
    var text_area_value = text_area.value;
    var regex           = /\n/g; 
    var essay           = text_area_value.replace(regex," ");
    var words           = essay.split(' ');
    
    for (z=0; z<words.length; z++){
        if (words[z].length>0){
            count++;
        }
    }
    
    count_element.value     = count;
    if (count>=wc) {
        /*text_area.value     = essay;*/

        var dataString = '';
        for (z=0; z<wc; z++){
	  if (words[z].length>0) {
	    dataString = dataString + words[z] + ' '; 
	  }
	}

	text_area.value = dataString; 
        text_area.blur();
	count = wc;
        count_element.value = count;
        alert("You have reached the "+ wc +" word limit.");
    }
}

function popUp(URL) {
  day = new Date();
  id  = day.getTime();
  eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=0,width=640,height=420,left = 202,top = 184');");
}

/**
 * Function to execute javascript that is assigned to element using innerHTML property
 *
 * @param elementName element name, that whose innerHTML is set
 */
function executeInnerHTML ( elementName ) 
{
    var element   = document.getElementById( elementName );
    var content   = element.getElementsByTagName('script');
    var tagLength = content.length;
    
    for (var x=0; x<tagLength; x++ ) {
	var newScript = document.createElement('script');
	newScript.type = "text/javascript";
	newScript.text = content[x].text;
	//execute script
	element.appendChild(newScript);
    }
    
    for ( var y=0; y<tagLength-1; y++ ) {
	element.removeChild(element.getElementsByTagName('script')[y]);
    }
}

function imagePopUp ( path ) 
{      window.open(path,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,screenX=150,screenY=150,top=150,left=150');
}

/**
 * Function to show / hide the row in optionFields
 *
 * @param element name index, that whose innerHTML is to hide else will show the hidden row.
 */
function showHideRow( index )
{
   if( index) {
    cj( 'tr#optionField_' + index ).hide( );
    if( cj( 'table#optionField tr:hidden:first' ).length )  cj( 'div#optionFieldLink' ).show( );
   } else {
    cj( 'table#optionField tr:hidden:first' ).show( );
    if( ! cj( 'table#optionField tr:hidden:last' ).length ) cj( 'div#optionFieldLink' ).hide( );
   }
    return false; 
}

/**
 * Function to check activity status in relavent to activity date
 *
 * @param element message JSON object.
 */
function activityStatus( message )
{
    var d = new Date(), time = [], i;
    var currentDateTime = d.getTime()
    var activityTime    = cj("input#activity_date_time_time").val().replace(":", "");
    
    //chunk the time in bunch of 2 (hours,minutes,ampm)
	for(i=0; i<activityTime.length; i+=2 ) { 
        time.push( activityTime.slice( i, i+2 ) );
    }
    var activityDate = new Date( cj("input#activity_date_time_hidden").val() );
      
    d.setFullYear(activityDate.getFullYear());
    d.setMonth(activityDate.getMonth());
    d.setDate(activityDate.getDate());
    var hours = time['0'];
    var ampm  = time['2'];

    if (ampm == "PM" && hours != 0 && hours != 12) {
        // force arithmetic instead of string concatenation
        hours = hours*1 + 12;
    } else if (ampm == "AM" && hours == 12) {
        hours = 0;
    }
    d.setHours(hours);
    d.setMinutes(time['1']);

    var activity_date_time = d.getTime();

    var activityStatusId = cj('#status_id').val();

    if ( activityStatusId == 2 && currentDateTime < activity_date_time ) {
        if (! confirm( message.completed )) {
            return false;
        }
    } else if ( activity_date_time && activityStatusId == 1 && currentDateTime >= activity_date_time ) {
        if (! confirm( message.scheduled )) {
            return false;
        }
    } 
}
;$(document).ready(function() {
	$('.messages').appendTo('#status .messages-container');
	if($('#status .messages-container').children().length > 0) {
		$('#status').append('<div id="status-handle"><span class="ui-icon ui-icon-arrowthickstop-1-n"></span></div>');
	}
	
	$('#status-handle').click(function(){
		$('.messages-container').toggle('fast');
		$('#status-handle .ui-icon').toggleClass('ui-icon-arrowthickstop-1-n');
		$('#status-handle .ui-icon').toggleClass('ui-icon-arrowthickstop-1-s');
	});
	
});

;$(document).ready(function() {
	adminNotLoaded = true;
	$('#civi-admin-link').click(function(){
		$('.primary-link').removeClass('primary-link-active');
		$(this).toggleClass('admin-link-active');
		$(this).toggleClass('active');
		var adminURL = $(this).attr('href');
		adminURL = adminURL;
		$('.civi-admin-block-wrapper').toggle();
		if(adminNotLoaded == true){
			$('.civi-admin-block').load(adminURL);
		}
		adminNotLoaded = false;
		
		return false;
	});

	$('.civi-advanced-search-link').click(function(){
		$(this).toggleClass('civi-adv-search-linkwrap-active');
		$('.civi-adv-search-body').toggle();
		return false;
	});

	$("input[name=sort_name]").focus(function(){
		var defaultText = $(this).val();
		if(defaultText === 'enter name or email'){
			$(this).val('');
			$(this).addClass('input-active');			
			}
		});
	$("input[name=sort_name]").blur(function(){
		var defaultText = $(this).val();
		if(defaultText === ''){
			$(this).val('enter name or email');
			$(this).removeClass('input-active');
			}
		});	

	$("#civi_text_search").focus(function(){
		var defaultText = $(this).val();
		if(defaultText === 'enter any text'){
			$(this).val('');
			$(this).addClass('input-active');			
			}
		});
	$("#civi_text_search").blur(function(){
		var defaultText = $(this).val();
		if(defaultText === ''){
			$(this).val('enter any text');
			$(this).removeClass('input-active');
			}
		});
		
	 $('body').click(function() {
	 	$('.primary-link').removeClass('primary-link-active');
	 });
	
	 $('.primary-link').click(function(event){
	     event.stopPropagation();
	 });
	
			
	$('.primary-link .main-menu-item').click( function(){
		$('.civi-admin-block-wrapper').hide();
		$('.admin-link a').removeClass('active'); 
		$('.primary-link').removeClass('primary-link-active');
		$(this).parent().toggleClass('primary-link-active')
		});
	
	$('#civicrm-menu').hide();
	if ($('#civicrm-menu').length >0){
		$('#civi-admin-menu').show();
		}
	$('#civi-admin-menu').click(function(){$('#civicrm-menu').toggle();});
	
	$('.civi-general-search').append('<div id="general-form-hack"></div>');
	$('#general-form-hack').hide()
		.load('/nyss/civicrm/contact/search/custom&csid=15&reset=1&snippet=1', 
			function(){
				$('#general-form-hack #Custom input[type=hidden]').appendTo('#gen-search-wrapper');
			});

	
	
});



DD_roundies.addRule('.create-link, .account-info-wrapper, .ac_results-inner, .civi-advanced-search-link, .civi-menu, .civi-admin-block, .civi-search-section .input-wrapper, .not-logged-in #block-user-0 #edit-submit, .standard-container', '4px');
DD_roundies.addRule('#crm-container .column-1 .widget-header, #footer #dashboard-link-wrapper', '4px 4px 0px 0px');
DD_roundies.addRule('#crm-container .column-1 .widget-content', '0px 0px 4px 4px');
;/**
* DD_roundies, this adds rounded-corner CSS in standard browsers and VML sublayers in IE that accomplish a similar appearance when comparing said browsers.
* Author: Drew Diller
* Email: drew.diller@gmail.com
* URL: http://www.dillerdesign.com/experiment/DD_roundies/
* Version: 0.0.2a -  preview 2008.12.26
* Licensed under the MIT License: http://dillerdesign.com/experiment/DD_roundies/#license
*
* Usage:
* DD_roundies.addRule('#doc .container', '10px 5px'); // selector and multiple radii
* DD_roundies.addRule('.box', 5, true); // selector, radius, and optional addition of border-radius code for standard browsers.
* 
* Just want the PNG fixing effect for IE6, and don't want to also use the DD_belatedPNG library?  Don't give any additional arguments after the CSS selector.
* DD_roundies.addRule('.your .example img');
**/

eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('t K={16:\'K\',1L:G,1M:G,1d:G,2f:y(){u(D.2g!=8&&D.1N&&!D.1N[q.16]){q.1L=M;q.1M=M}17 u(D.2g==8){q.1d=M}},2h:D.2i,1O:[],1b:{},2j:y(){u(q.1L||q.1M){D.1N.2L(q.16,\'2M:2N-2O-2P:x\')}u(q.1d){D.2Q(\'<?2R 2S="\'+q.16+\'" 2T="#1P#2k" ?>\')}},2l:y(){t a=D.1k(\'z\');D.2m.1w.1Q(a,D.2m.1w.1w);u(a.12){2n{t b=a.12;b.1x(q.16+\'\\\\:*\',\'{1l:2U(#1P#2k)}\');q.12=b}2o(2p){}}17{q.12=a}},1x:y(a,b,c){u(1R b==\'1S\'||b===2V){b=0}u(b.2W.2q().1y(\'2X\')==-1){b=b.2q().2Y(/[^0-9 ]/g,\'\').1T(\' \')}H(t i=0;i<4;i++){b[i]=(!b[i]&&b[i]!==0)?b[C.1e((i-2),0)]:b[i]}u(q.12){u(q.12.1x){t d=a.1T(\',\');H(t i=0;i<d.1U;i++){q.12.1x(d[i],\'1l:2Z(K.1V.2r(q, [\'+b.1W(\',\')+\']))\')}}17 u(c){t e=b.1W(\'F \')+\'F\';q.12.1z(D.2s(a+\' {Q-1f:\'+e+\'; -30-Q-1f:\'+e+\';}\'));q.12.1z(D.2s(a+\' {-1A-Q-1m-1n-1f:\'+b[0]+\'F \'+b[0]+\'F; -1A-Q-1m-1X-1f:\'+b[1]+\'F \'+b[1]+\'F; -1A-Q-1Y-1X-1f:\'+b[2]+\'F \'+b[2]+\'F; -1A-Q-1Y-1n-1f:\'+b[3]+\'F \'+b[3]+\'F;}\'))}}17 u(q.1d){q.1O.31({\'2t\':a,\'2u\':b})}},2v:y(a){2w(32.33){I\'z.Q\':I\'z.34\':I\'z.1B\':q.1o(a);13;I\'z.2x\':q.1Z(a);13;I\'z.1p\':I\'z.2y\':I\'z.2z\':q.1o(a);13;I\'z.20\':a.18.z.20=(a.z.20==\'S\')?\'S\':\'35\';13;I\'z.21\':q.22(a);13;I\'z.1c\':a.18.z.1c=a.z.1c;13}},1o:y(a){a.14.23=\'\';q.2A(a);q.1Z(a);q.1C(a);q.1D(a);q.24(a);q.2B(a);q.22(a)},22:y(a){u(a.W.21.1y(\'36\')!=-1){t b=a.W.21;b=1g(b.37(b.25(\'=\')+1,b.25(\')\')),10)/2C;H(t v 1h a.x){a.x[v].1i.38=b}}},2A:y(a){u(!a.W){1q}17{t b=a.W}a.14.1p=\'\';a.14.1E=\'\';t c=(b.1p==\'2D\');t d=M;u(b.1E!=\'S\'||a.1F){u(!a.1F){a.J=b.1E;a.J=a.J.39(5,a.J.25(\'")\')-5)}17{a.J=a.26}t e=q;u(!e.1b[a.J]){t f=D.1k(\'3a\');f.1r(\'3b\',y(){q.1s=q.3c;q.1t=q.3d;e.1D(a)});f.3e=e.16+\'3f\';f.14.23=\'1l:S; 1j:27; 1m:-2E; 1n:-2E; Q:S;\';f.26=a.J;f.2F(\'1s\');f.2F(\'1t\');D.2G.1Q(f,D.2G.1w);e.1b[a.J]=f}a.x.Z.1i.26=a.J;d=G}a.x.Z.2H=!d;a.x.Z.1G=\'S\';a.x.1u.2H=!c;a.x.1u.1G=b.1p;a.14.1E=\'S\';a.14.1p=\'2D\'},1Z:y(a){a.x.1H.1G=a.W.2x},1C:y(a){t c=[\'N\',\'19\',\'1a\',\'O\'];a.P={};H(t b=0;b<4;b++){a.P[c[b]]=1g(a.W[\'Q\'+c[b]+\'U\'],10)||0}},1D:y(c){t e=[\'O\',\'N\',\'U\',\'V\'];H(t d=0;d<4;d++){c.E[e[d]]=c[\'3g\'+e[d]]}t f=y(a,b){a.z.1n=(b?0:c.E.O)+\'F\';a.z.1m=(b?0:c.E.N)+\'F\';a.z.1s=c.E.U+\'F\';a.z.1t=c.E.V+\'F\'};H(t v 1h c.x){t g=(v==\'Z\')?1:2;c.x[v].3h=(c.E.U*g)+\', \'+(c.E.V*g);f(c.x[v],M)}f(c.18,G);u(K.1d){c.x.1H.z.28=\'-3i\';u(1R c.P==\'1S\'){q.1C(c)}c.x.1u.z.28=(c.P.N-1)+\'F \'+(c.P.O-1)+\'F\'}},24:y(j){t k=y(a,w,h,r,b,c,d){t e=a?[\'m\',\'1I\',\'l\',\'1J\',\'l\',\'1I\',\'l\',\'1J\',\'l\']:[\'1J\',\'l\',\'1I\',\'l\',\'1J\',\'l\',\'1I\',\'l\',\'m\'];b*=d;c*=d;w*=d;h*=d;t R=r.2I();H(t i=0;i<4;i++){R[i]*=d;R[i]=C.3j(w/2,h/2,R[i])}t f=[e[0]+C.11(0+b)+\',\'+C.11(R[0]+c),e[1]+C.11(R[0]+b)+\',\'+C.11(0+c),e[2]+C.15(w-R[1]+b)+\',\'+C.11(0+c),e[3]+C.15(w+b)+\',\'+C.11(R[1]+c),e[4]+C.15(w+b)+\',\'+C.15(h-R[2]+c),e[5]+C.15(w-R[2]+b)+\',\'+C.15(h+c),e[6]+C.11(R[3]+b)+\',\'+C.15(h+c),e[7]+C.11(0+b)+\',\'+C.15(h-R[3]+c),e[8]+C.11(0+b)+\',\'+C.11(R[0]+c)];u(!a){f.3k()}t g=f.1W(\'\');1q g};u(1R j.P==\'1S\'){q.1C(j)}t l=j.P;t m=j.2J.2I();t n=k(M,j.E.U,j.E.V,m,0,0,2);m[0]-=C.1e(l.O,l.N);m[1]-=C.1e(l.N,l.19);m[2]-=C.1e(l.19,l.1a);m[3]-=C.1e(l.1a,l.O);H(t i=0;i<4;i++){m[i]=C.1e(m[i],0)}t o=k(G,j.E.U-l.O-l.19,j.E.V-l.N-l.1a,m,l.O,l.N,2);t p=k(M,j.E.U-l.O-l.19+1,j.E.V-l.N-l.1a+1,m,l.O,l.N,1);j.x.1u.29=o;j.x.Z.29=p;j.x.1H.29=n+o;q.2K(j)},2B:y(a){t s=a.W;t b=[\'N\',\'O\',\'19\',\'1a\'];H(t i=0;i<4;i++){a.14[\'1B\'+b[i]]=(1g(s[\'1B\'+b[i]],10)||0)+(1g(s[\'Q\'+b[i]+\'U\'],10)||0)+\'F\'}a.14.Q=\'S\'},2K:y(e){t f=K;u(!e.J||!f.1b[e.J]){1q}t g=e.W;t h={\'X\':0,\'Y\':0};t i=y(a,b){t c=M;2w(b){I\'1n\':I\'1m\':h[a]=0;13;I\'3l\':h[a]=0.5;13;I\'1X\':I\'1Y\':h[a]=1;13;1P:u(b.1y(\'%\')!=-1){h[a]=1g(b,10)*0.3m}17{c=G}}t d=(a==\'X\');h[a]=C.15(c?((e.E[d?\'U\':\'V\']-(e.P[d?\'O\':\'N\']+e.P[d?\'19\':\'1a\']))*h[a])-(f.1b[e.J][d?\'1s\':\'1t\']*h[a]):1g(b,10));h[a]+=1};H(t b 1h h){i(b,g[\'2y\'+b])}e.x.Z.1i.1j=(h.X/(e.E.U-e.P.O-e.P.19+1))+\',\'+(h.Y/(e.E.V-e.P.N-e.P.1a+1));t j=g.2z;t c={\'T\':1,\'R\':e.E.U+1,\'B\':e.E.V+1,\'L\':1};t k={\'X\':{\'2a\':\'L\',\'2b\':\'R\',\'d\':\'U\'},\'Y\':{\'2a\':\'T\',\'2b\':\'B\',\'d\':\'V\'}};u(j!=\'2c\'){c={\'T\':(h.Y),\'R\':(h.X+f.1b[e.J].1s),\'B\':(h.Y+f.1b[e.J].1t),\'L\':(h.X)};u(j.1y(\'2c-\')!=-1){t v=j.1T(\'2c-\')[1].3n();c[k[v].2a]=1;c[k[v].2b]=e.E[k[v].d]+1}u(c.B>e.E.V){c.B=e.E.V+1}}e.x.Z.z.3o=\'3p(\'+c.T+\'F \'+c.R+\'F \'+c.B+\'F \'+c.L+\'F)\'},1v:y(a){t b=q;2d(y(){b.1o(a)},1)},2e:y(a){q.1D(a);q.24(a)},1V:y(b){q.z.1l=\'S\';u(!q.W){1q}17{t c=q.W}t d={3q:G,3r:G,3s:G,3t:G,3u:G,3v:G,3w:G};u(d[q.1K]===G){1q}t e=q;t f=K;q.2J=b;q.E={};t g={3x:\'2e\',3y:\'2e\'};u(q.1K==\'A\'){t i={3z:\'1v\',3A:\'1v\',3B:\'1v\',3C:\'1v\'};H(t a 1h i){g[a]=i[a]}}H(t h 1h g){q.1r(\'3D\'+h,y(){f[g[h]](e)})}q.1r(\'3E\',y(){f.2v(e)});t j=y(a){a.z.3F=1;u(a.W.1j==\'3G\'){a.z.1j=\'3H\'}};j(q.3I);j(q);q.18=D.1k(\'3J\');q.18.14.23=\'1l:S; 1j:27; 28:0; 1B:0; Q:0; 3K:S;\';q.18.z.1c=c.1c;q.x={\'1u\':M,\'Z\':M,\'1H\':M};H(t v 1h q.x){q.x[v]=D.1k(f.16+\':3L\');q.x[v].1i=D.1k(f.16+\':3M\');q.x[v].1z(q.x[v].1i);q.x[v].3N=G;q.x[v].z.1j=\'27\';q.x[v].z.1c=c.1c;q.x[v].3O=\'1,1\';q.18.1z(q.x[v])}q.x.Z.1G=\'S\';q.x.Z.1i.3P=\'3Q\';q.3R.1Q(q.18,q);q.1F=G;u(q.1K==\'3S\'){q.1F=M;q.z.3T=\'3U\'}2d(y(){f.1o(e)},1)}};2n{D.3V("3W",G,M)}2o(2p){}K.2f();K.2j();K.2l();u(K.1d&&D.1r&&K.2h){D.1r(\'3X\',y(){u(D.3Y==\'3Z\'){t d=K.1O;t e=d.1U;t f=y(a,b,c){2d(y(){K.1V.2r(a,b)},c*2C)};H(t i=0;i<e;i++){t g=D.2i(d[i].2t);t h=g.1U;H(t r=0;r<h;r++){u(g[r].1K!=\'40\'){f(g[r],d[i].2u,r)}}}}})}',62,249,'||||||||||||||||||||||||||this|||var|if|||vml|function|style|||Math|document|dim|px|false|for|case|vmlBg|DD_roundies||true|Top|Left|bW|border||none||Width|Height|currentStyle|||image||floor|styleSheet|break|runtimeStyle|ceil|ns|else|vmlBox|Right|Bottom|imgSize|zIndex|IE8|max|radius|parseInt|in|filler|position|createElement|behavior|top|left|applyVML|backgroundColor|return|attachEvent|width|height|color|pseudoClass|firstChild|addRule|search|appendChild|webkit|padding|vmlStrokeWeight|vmlOffsets|backgroundImage|isImg|fillcolor|stroke|qy|qx|nodeName|IE6|IE7|namespaces|selectorsToProcess|default|insertBefore|typeof|undefined|split|length|roundify|join|right|bottom|vmlStrokeColor|display|filter|vmlOpacity|cssText|vmlPath|lastIndexOf|src|absolute|margin|path|b1|b2|repeat|setTimeout|reposition|IEversion|documentMode|querySelector|querySelectorAll|createVmlNameSpace|VML|createVmlStyleSheet|documentElement|try|catch|err|toString|call|createTextNode|selector|radii|readPropertyChanges|switch|borderColor|backgroundPosition|backgroundRepeat|vmlFill|nixBorder|100|transparent|10000px|removeAttribute|body|filled|slice|DD_radii|clipImage|add|urn|schemas|microsoft|com|writeln|import|namespace|implementation|url|null|constructor|Array|replace|expression|moz|push|event|propertyName|borderWidth|block|lpha|substring|opacity|substr|img|onload|offsetWidth|offsetHeight|className|_sizeFinder|offset|coordsize|1px|min|reverse|center|01|toUpperCase|clip|rect|BODY|TABLE|TR|TD|SELECT|OPTION|TEXTAREA|resize|move|mouseleave|mouseenter|focus|blur|on|onpropertychange|zoom|static|relative|offsetParent|ignore|background|shape|fill|stroked|coordorigin|type|tile|parentNode|IMG|visibility|hidden|execCommand|BackgroundImageCache|onreadystatechange|readyState|complete|INPUT'.split('|'),0,{}))


/* calling rounded corners */






// generic JS fixes

// various JavaScript object.
var Blueprint = {};

// jump to the value in a select drop down
Blueprint.go = function(e) {
  var destination = e.options[e.selectedIndex].value;
  if (destination && destination != 0) location.href = destination;
};

// prevent users from clicking a submit button twice
Blueprint.formCheck = function() {
  // only apply this to node and comment and new user registration forms
  var forms = $("#node-form>div>div>#edit-submit,#comment-form>div>#edit-submit,#user-register>div>#edit-submit");

  // insert the saving div now to cache it for better performance and to show the loading image
  $('<div id="saving"><p class="saving">Saving&hellip;</p></div>').insertAfter(forms);

  forms.click(function() {
    $(this).siblings("input:submit").hide();
    $(this).hide();
    $("#saving").show();

    var notice = function() {
      $('<p id="saving-notice">Not saving? Wait a few seconds, reload this page, and try again. Every now and then the internet hiccups too :-)</p>').appendTo("#saving").fadeIn();
    };

    // append notice if form saving isn't work, perhaps a timeout issue
    setTimeout(notice, 24000);
  });
};

// Global Killswitch.
if (Drupal.jsEnabled) {
  $(document).ready(Blueprint.formCheck);
};