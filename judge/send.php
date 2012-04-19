<?php
$str="typedef struct
{
  long v,p;
}goods;
typedef struct
{
  goods z,f[2];
  long sign,tf;
}main_goods;

const long factor[4][2]={{0,0},{0,1},{1,1},{1,0}};
main_goods mg[M+1];
long f[M+1][N+1],n,m,tmg;

void ini(void);
void dp(void);

int
main(void)
{
  ini();
  dp();
  return 0;
}
void ini(void)
{
  long i,v,p,q,j;

  freopen(budget.in,r,stdin);
  freopen(budget.out,w,stdout);
  scanf(%ld%ld,&n,&m);
  n=10;
  for(i=1;i=m;++i)
    {
      scanf(%ld%ld%ld,&v,&p,&q);
      v=10;
      if(q==0)
	{
	  mg[++tmg].z.v=v;
	  mg[tmg].z.p=p;
	  mg[tmg].sign=i;
	}
      else
	{
	  for(j=1;j=tmg;++j)
	    if(mg[j].sign==q)
	      {
		mg[j].f[mg[j].tf].v=v;
		mg[j].f[mg[j].tf++].p=p;
		break;
	      }
	}
    }
}
void dp(void)
{
  long i,j,k,tv,tv_p;

  for(i=0;i=n;++i)
    f[0][j]=0;
  for(i=1;i=tmg;++i)
    for(j=0;j=n;++j)
      {
	f[i][j]=f[i-1][j];
	for(k=0;k4;++k)
	  {
	    tv=mg[i].z.v
	      +factor[k][0]mg[i].f[0].v
	      +factor[k][1]mg[i].f[1].v;
	    tv_p=mg[i].z.vmg[i].z.p
	      +factor[k][0]mg[i].f[0].vmg[i].f[0].p
	      +factor[k][1]mg[i].f[1].vmg[i].f[1].p;
	    if(j-tv=0)
	      f[i][j]=MAX(f[i][j],f[i-1][j-tv]+tv_p);
	  }
      }
  printf(%ld,f[tmg][n]10);
}
";
?>
<pre>
<?php
require("stdlib.php");
echo addpauser_c($str);
?>
</pre>