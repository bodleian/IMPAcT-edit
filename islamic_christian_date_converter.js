// Original version (c) J. Thomann 1996. See http://www.oriold.uzh.ch/static/hegira.html.
// This version slightly adapted for integration into IMPACT editing interface, by kind permission of J. Thomann. 

function intPart(floatNum){
if (floatNum< -0.0000001){
	 return Math.ceil(floatNum-0.0000001)
	}
return Math.floor(floatNum+0.0000001)	
}
function weekDay(wdn){
					if(wdn==0){
						return "Monday"
						}
					if(wdn==1){
						return "Tuesday"
						}
					if(wdn==2){
						return "Wednesday"
						}
					if(wdn==3){
						return "Thursday"
						}
					if(wdn==4){
						return "Friday"
						}
					if(wdn==5){
						return "Saturday"
						}
					if(wdn==6){
						return "Sunday"
						}
	return ""

}
function chrToIsl(arg) {
	d=parseInt(arg.CDay.value)
	m=parseInt(arg.CMonth.value)
	y=parseInt(arg.CYear.value)
					if ((y>1582)||((y==1582)&&(m>10))||((y==1582)&&(m==10)&&(d>14))) 
						{
						jd=intPart((1461*(y+4800+intPart((m-14)/12)))/4)+intPart((367*(m-2-12*(intPart((m-14)/12))))/12)-
	intPart( (3* (intPart(  (y+4900+    intPart( (m-14)/12)     )/100)    )   ) /4)+d-32075
						}
						else
						{
						jd = 367*y-intPart((7*(y+5001+intPart((m-9)/7)))/4)+intPart((275*m)/9)+d+1729777
						}
					arg.JD.value=jd
					arg.wd.value=weekDay(jd%7)
					l=jd-1948440+10632
					n=intPart((l-1)/10631)
					l=l-10631*n+354
					j=(intPart((10985-l)/5316))*(intPart((50*l)/17719))+(intPart(l/5670))*(intPart((43*l)/15238))
					l=l-(intPart((30-j)/15))*(intPart((17719*j)/50))-(intPart(j/16))*(intPart((15238*j)/43))+29
					m=intPart((24*l)/709)
					d=l-intPart((709*m)/24)
					y=30*n+j-30

	arg.HDay.value=d
	arg.HMonth.value=m
	arg.HYear.value=y
}
function islToChr( HYear, HMonth, HDay ) {
	d=parseInt(HDay)
	m=parseInt(HMonth)
	y=parseInt(HYear)
	jd=intPart((11*y+3)/30)+354*y+30*m-intPart((m-1)/2)+d+1948440-385
	//arg.JD.value=jd 
	//arg.wd.value=weekDay(jd%7)
					if (jd> 2299160 )
						{
						 l=jd+68569
						 n=intPart((4*l)/146097)
						l=l-intPart((146097*n+3)/4)
						 i=intPart((4000*(l+1))/1461001)
						l=l-intPart((1461*i)/4)+31
						 j=intPart((80*l)/2447)
						d=l-intPart((2447*j)/80)
						l=intPart(j/11)
						m=j+2-12*l
						y=100*(n-49)+i+l
						}	
					else	
						{
						 j=jd+1402
						 k=intPart((j-1)/1461)
						 l=j-1461*k
						 n=intPart((l-1)/365)-intPart(l/1461)
						 i=l-365*n+30
						j=intPart((80*i)/2447)
						d=i-intPart((2447*j)/80)
						i=intPart(j/11)
						m=j+2-12*i
						y=4*k+n+i-4716
						}

  if( m < 10 ) {
    m = "0" + m
  }

  if( d < 10 ) {
    d = "0" + d
  }

	var CDate = y + "-" + m + "-" + d
	return CDate

}

