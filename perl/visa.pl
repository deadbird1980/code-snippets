use LWP 5.64;
use strict;
use warnings;
use LWP::UserAgent;
use MIME::Lite;
# open file and disable buffering
my $fh;
open($fh, "/home/joey/visa_result.log");
my $result = '';
if ($fh) {
  $result = <$fh>;
  close($fh);
}
open($fh, ">", "/home/joey/visa_result.log");
# open file and disable buffering
my $browser = LWP::UserAgent->new;
$browser->default_header('User-Agent' => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3');
$browser->default_header('Connection' => 'keep-alive');
$browser->default_header('Accept-Encoding' => scalar HTTP::Message::decodable());
$browser->default_header('Accept-Language' => "en-us, en");
$browser->default_header('Cookie' => 'ASP.NET_SessionId=r0f0om45hxwy5n55s5ra2qav');
#$browser->default_header('Keep-Alive' => "115");
$browser->requests_redirectable(['POST']);
#$browser->cookie_jar({ file => "$ENV{HOME}/.cookies.txt" });
$browser->cookie_jar();

my $url =
    'https://www.vfs.org.in/UKG-PassportTracking-PPT/ApplicantTrackStatus.aspx?Data=eQhlWV1635htWhLZVVgaKw%3d%3d&lang=XlDVhFJC4oE%3d';
my $response = $browser->post($url,
["__VIEWSTATE"=>"Ci0r4SwI8I3GsbYRII1hTSWF1oaEPoffY16WRP8nEKOCRi9W64GhQ4YmBuRywUgUasxUTQ1MPN5PYgtz/BeXZw==",
"txtRefNO"=>"SANA/541010/021441/1",
"cmdSubmit"=>"Submit",
"txtDat"=>"01",
"txtMont"=>"10",
"txtYea"=>"1961",
"__VIEWSTATEENCRYPTED"=>"",
"__EVENTVALIDATION"=>"Y0/lbu5KciBk+4YJl518/xOlhZ/A9ZeUzJVOgOduBmOU2xcd5WB645yCkJYHog/IJL5lAFmVCwc+si/T9OYZJg=="]
);

die "$url error: ", $response->status_line
  unless $response->is_success;
die "Weird content type at $url -- ", $response->content_type
  unless $response->content_type eq 'text/html';
 
my $str = $response->content();
if ($str =~  /<span id="lblScanStatus" style="display:inline-block;width:300px;">(.*)<\/span>/) {
  if ($result ne $1) {
    #system("/bin/mail joeyw\@reallyenglish.com -s 'visa status change' < visa_result.log ");
    my $message = MIME::Lite->new(
          To => 'aa1980@gmail.com',
          CC => 'dd1980@gmail.com',
          Subject => 'visa status changed',
          Data => $1
    );
    $message->send();
  }
  print $fh $1;
}
