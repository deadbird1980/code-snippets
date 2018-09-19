#include <iostream>
#include <cstring>
#include <stdlib.h>
#include <fstream>
#include <iconv.h>
#include <errno.h>
using namespace std;

char * convert( char *from_charset, char *to_charset, char *input )
{
    size_t input_size, output_size, bytes_converted;
    char * output;
    char * tmp;
    iconv_t cd;

    cd = iconv_open( to_charset, from_charset);
    if (cd == (iconv_t)-1) {
	switch (errno) {
	  case EMFILE:
	  case ENFILE:
	  case ENOMEM:
        cd = iconv_open( to_charset, from_charset);
	}
	}
	if (cd == (iconv_t)-1) {
	    int inval = errno == EINVAL;
    }

    input_size = strlen(input);
    output_size = 2 * input_size;
    output = (char*) malloc(output_size+1);

    bytes_converted = iconv(cd, &input, &input_size, &output, &output_size);
	switch (errno) {
	  case E2BIG:
	    /* try the left in next loop */
        fprintf(stderr,"error: illegal sequence\n");
	    break;
	  case EILSEQ:
        fprintf(stderr,"error: illegal sequence\n");
        break;
	  case EINVAL:
        fprintf(stderr,"error: invalid\n");
        break;
	  case 0:
        fprintf(stderr,"error: broken lib\n");
        break;
	  default:
        fprintf(stderr,"error: fail lib\n");
        break;
	}
    fprintf(stderr,"out: %s length: %d\n",output, (int)output_size);
    cout << "Bytes converted: " << bytes_converted << endl;
    if ( iconv_close (cd) != 0)
        cout<< "Error closing iconv_error" << endl;

    return output;
}


void print_hex(char *str, int len) {
    fprintf(stderr,"length=%d\n", len);

    for(int i=0; i< len; ++i) {
        fprintf(stderr,"%x",str[i] & 0xff);
    }
    fprintf(stderr,"\n");
}

int main(int argc, char *argv[])
{
    //char src[] = "龅";
    char src[] = "a";
    //char src[] = "\x00a";
    char dst[100];
    char dst_b[100];
    memset(dst, '\0', sizeof(dst));
    memset(dst_b, '\0', sizeof(dst_b));
    size_t srclen = strlen(src);
    size_t srclen_b = srclen*2;
    size_t dstlen = srclen*2;
    convert("UTF-16//TRANSIT//IGNORE","CP1252", src);

    fprintf(stderr,"in: %s length: %d\n",src, (int)srclen);
    print_hex(src, srclen);
 
    char * pIn = src;
    char * pOut = ( char*)dst;
    char * pOut_b = ( char*)dst_b;
 
    errno = 0;
    iconv_t conv = iconv_open("UTF-16//TRANSIT//IGNORE","UTF-8");
    if (conv == (iconv_t)-1) {
	switch (errno) {
	  case EMFILE:
	  case ENFILE:
	  case ENOMEM:
        conv = iconv_open("UTF-16//TRANSIT//IGNORE","UTF-8");
	}
	}
	if (conv == (iconv_t)-1) {
	    int inval = errno == EINVAL;
    }
    iconv(conv, &pIn, &srclen, &pOut, &dstlen);
    fprintf(stderr,"error: %d out: %d length: %d\n",errno, (int)(sizeof dst), (int)dstlen);
	switch (errno) {
	  case E2BIG:
	    /* try the left in next loop */
        fprintf(stderr,"error: illegal sequence\n");
	    break;
	  case EILSEQ:
        fprintf(stderr,"error: illegal sequence\n");
        break;
	  case EINVAL:
        fprintf(stderr,"error: invalid\n");
        break;
	  case 0:
        fprintf(stderr,"error: broken lib\n");
        break;
	  default:
        fprintf(stderr,"error: fail lib\n");
        break;
	}
    fprintf(stderr,"out: %s length: %d\n",dst, (int)dstlen);
    iconv_t convb = iconv_open("UTF-8","UTF-16");
    dst[sizeof dst - dstlen] = 0;
    print_hex(dst, dstlen+4);
    iconv(convb, &pOut, &srclen_b, &pOut_b, &dstlen);
    dst_b[sizeof dst_b - dstlen] = 0;
    fprintf(stderr,"out: %s length: %d\n",dst_b, (int)dstlen);
    print_hex(dst_b, dstlen);
    iconv_close(conv);
    iconv_close(convb);
}
