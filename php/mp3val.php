<?php

class MPEGINFO {

//MPEG frames counts
    public $mpeg1layer1;
    public $mpeg1layer2;
    public $mpeg1layer3;
    public $mpeg2layer1;
    public $mpeg2layer2;
    public $mpeg2layer3;
    public $mpeg25layer1;
    public $mpeg25layer2;
    public $mpeg25layer3;
//Tag counts
    public $id3v1;
    public $id3v2;
    public $apev2;
//VBR header info
    public $VBRHeaderPresent;
    public $IsXingHeader; //otherwise it's Fraunhofer VBRI header
    public $BytesPresent;
    public $iBytes;
    public $FramesPresent;
    public $iFrames;
    public $iFirstMPEGFrameSize; //because Foobar2000 doesn't count the first frame (with Xing header) as MPEG data
//Error flags
    public $riff;
    public $unknown_format;
    public $truncated;
    public $mpeg_stream_error;
    public $garbage_at_the_begin;
    public $garbage_at_the_end;
//MPEG-related data
    public $LastFrameStereo;
    public $bLastFrameCRC;
    public $bCRC;
    public $bCRCError;
    public $iLastBitrate;
    public $iLastMPEGLayer;
    public $iLastMPEGVersion;
//Miscellaneous data
    public $bVariableBitrate;
    public $iTotalMPEGBytes;
    public $iErrors;
    public $iDeletedFrames;
    public $iCRCErrors;

    public function __construct() {
        $this->clear();
    }

    public function clear() {
        $this->mpeg1layer1 = 0;
        $this->mpeg1layer2 = 0;
        $this->mpeg1layer3 = 0;
        $this->mpeg2layer1 = 0;
        $this->mpeg2layer2 = 0;
        $this->mpeg2layer3 = 0;
        $this->mpeg25layer1 = 0;
        $this->mpeg25layer2 = 0;
        $this->mpeg25layer3 = 0;
        $this->id3v1 = 0;
        $this->id3v2 = 0;
        $this->apev2 = 0;

        $this->VBRHeaderPresent = false;
        $this->IsXingHeader = true;
        $this->BytesPresent = false;
        $this->iBytes = -1;
        $this->FramesPresent = false;
        $this->iFrames = -1;

        $this->riff = -1;
        $this->unknown_format = -1;
        $this->mpeg_stream_error = -1;
        $this->truncated = -1;
        $this->garbage_at_the_begin = -1;
        $this->garbage_at_the_end = -1;

        $this->LastFrameStereo = false;
        $this->bLastFrameCRC = false;
        $this->bCRC = false;
        $this->bCRCError = false;
        $this->bVariableBitrate = false;
        $this->iLastBitrate = -2;
        $this->iLastMPEGLayer = 0;
        $this->iLastMPEGVersion = 0;
        $this->iCRCErrors = 0;

        $this->iTotalMPEGBytes = 0;
        $this->iErrors = 0;
        $this->iDeletedFrames = 0;
    }

}

class mp3Validator {

    private $mpeg1layer1_bitrates = array(-1, 32, 64, 96, 128, 160, 192, 224, 256, 288, 320, 352, 384, 416, 448, -1);
    private $mpeg1layer2_bitrates = array(-1, 32, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320, 384, -1);
    private $mpeg1layer3_bitrates = array(-1, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320, -1);
    private $mpeg2layer1_bitrates = array(-1, 32, 48, 56, 64, 80, 96, 112, 128, 144, 160, 176, 192, 224, 256, -1);
    private $mpeg2layers23_bitrates = array(-1, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160, -1);
    private $mpginfo;
    private $bytes = '';
    private $warnings = array();
    private $errors = array();
    private $mpeg_total = 0;

    public function __construct($data) {
        $this->mpginfo = new MPEGINFO();
        $this->bytes = $data;
        $this->validate();
    }

    public function isValid() {

        return count($this->errors) == 0;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getWarnings() {
        return $this->warnings;
    }

    public function getMPEGTotal() {
        return $this->mpeg_total;
    }

    private function CalculateCRC16($init, $polynom, $buf, $cb) {
        $crc = $init & 0xFFFF;

        for ($i = 0; $i <= $cb - 1; $i++) {
            $crc<<=8;
            $crc[2]^=$buf[$i];
            for ($j = 0; $j <= 7; $j++) {
                if (($crc << $j) & 0x00800000) {
                    $crc^=($polynom << (7 - $j));
                }
            }
            $crc&=0xFFFF;
        }
        return $crc;
    }

    private function MPEGResync($baseptr, $index, $iFileSize, $frames) {
        $p = $index;
        $sync_frames = 0;
        $new_frame = 0;
        $iFrameSize = 0;
        $tmp_mpginfo = new MPEGINFO();
        $iMPEGVersion = 0;
        $iMPEGLayer = 0;

        do {
            if ($iFileSize - $p - 3 <= 0)
                return -1;
            $p = strpos($baseptr, "\xFF", $p);
            if ($p === false)
                break;
            if ((ord($baseptr[$p + 1]) & 0xE0) != 0xE0) {
                $p++;
                $sync_frames = 0;
                $iMPEGVersion = 0;
                $iMPEGLayer = 0;
                continue;
            }
            $iFrameSize = $this->ValidateMPEGFrame($baseptr, $p, $tmp_mpginfo);
            if ($iFrameSize == -1) {
                $p++;
                $sync_frames = 0;
                $iMPEGVersion = 0;
                $iMPEGLayer = 0;
                continue;
            }
            $new_frame = $iFrameSize + $p;
            $sync_frames++;
            $iMPEGVersion = $tmp_mpginfo->iLastMPEGVersion;
            $iMPEGLayer = $tmp_mpginfo->iLastMPEGLayer;
            while ($sync_frames < $frames) {
                if ($new_frame + 4 > $iFileSize) {
                    $sync_frames = 0;
                    $iMPEGVersion = 0;
                    $iMPEGLayer = 0;
                    $p++;
                    break;
                }
                $iFrameSize = $this->ValidateMPEGFrame($baseptr, $new_frame, $tmp_mpginfo);
                if ($iFrameSize == -1) {
                    $sync_frames = 0;
                    $iMPEGVersion = 0;
                    $iMPEGLayer = 0;
                    $p++;
                    break;
                }
                $new_frame+=$iFrameSize;
                $sync_frames++;
                if ($iMPEGVersion && $iMPEGVersion != $tmp_mpginfo->iLastMPEGVersion) {
                    $sync_frames = 0;
                    $iMPEGVersion = 0;
                    $iMPEGLayer = 0;
                    $p++;
                    break;
                }
                if ($iMPEGLayer && $iMPEGLayer != $tmp_mpginfo->iLastMPEGLayer) {
                    $sync_frames = 0;
                    $iMPEGVersion = 0;
                    $iMPEGLayer = 0;
                    $p++;
                    break;
                }
            }
        } while ($sync_frames < $frames);

        if ($sync_frames >= $frames)
            return $p;
        return -1;
    }

    private function ValidateAPEv2Tag($baseptr, $index, $mpginfo) {
        $mpginfo->apev2++;
        #return *((int *)&baseptr[index+12])+32;
        list(,$num) = unpack("I*",substr($baseptr,$index+12,4));
        return $num + 32;
    }

    private function ValidateMPEGFrame($baseptr, $index, $mpginfo) {

        $mpeg_version = 0;
        $mpeg_layer = 0;
        $mpeg_bitrate = 0;

        $iFrameSize = 0;

//Check if the frame contains CRC
        if (ord($baseptr[$index + 1]) & 0x01) {
            $mpginfo->bLastFrameCRC = false;
        } else {
            $mpginfo->bLastFrameCRC = true;
            $mpginfo->bCRC = true;
        }

// Determine MPEG version and layer
        switch ((ord($baseptr[$index + 1]) >> 1) & 0x0F) {
            case 0x0F:
                $mpginfo->mpeg1layer1++;
                $mpeg_version = 1;
                $mpeg_layer = 1;
                break;
            case 0x0E:
                $mpginfo->mpeg1layer2++;
                $mpeg_version = 1;
                $mpeg_layer = 2;
                break;
            case 0x0D:
                $mpginfo->mpeg1layer3++;
                $mpeg_version = 1;
                $mpeg_layer = 3;
                break;
            case 0x0B:
                $mpginfo->mpeg2layer1++;
                $mpeg_version = 2;
                $mpeg_layer = 1;
                break;
            case 0x0A:
                $mpginfo->mpeg2layer2++;
                $mpeg_version = 2;
                $mpeg_layer = 2;
                break;
            case 0x09:
                $mpginfo->mpeg2layer3++;
                $mpeg_version = 2;
                $mpeg_layer = 3;
                break;
            case 0x03:
                $mpginfo->mpeg25layer1++;
                $mpeg_version = 25;
                $mpeg_layer = 1;
                break;
            case 0x02:
                $mpginfo->mpeg25layer2++;
                $mpeg_version = 25;
                $mpeg_layer = 2;
                break;
            case 0x01:
                $mpginfo->mpeg25layer3++;
                $mpeg_version = 25;
                $mpeg_layer = 3;
                break;
            default:
                $mpginfo->mpeg_stream_error = $index;
                return -1;
        }
// Calculate bit rate
        $bitrate_index = ((ord($baseptr[$index + 2])) >> 4) & 0x0F;
        if ($mpeg_version == 1) {
            if ($mpeg_layer == 1) {
                $mpeg_bitrate = $this->mpeg1layer1_bitrates[$bitrate_index];
            } else if ($mpeg_layer == 2) {
                $mpeg_bitrate = $this->mpeg1layer2_bitrates[$bitrate_index];
            } else {
                $mpeg_bitrate = $this->mpeg1layer3_bitrates[$bitrate_index];
            }
        } else {
            if ($mpeg_layer == 1) {
                $mpeg_bitrate = $this->mpeg2layer1_bitrates[$bitrate_index];
            } else {
                $mpeg_bitrate = $this->mpeg2layers23_bitrates[$bitrate_index];
            }
        }

        if ($mpeg_bitrate == -1) {
            $mpginfo->mpeg_stream_error = $index;
            return -1;
        }

        if ($mpginfo->iLastBitrate > 0 && $mpginfo->iLastBitrate != $mpeg_bitrate) {
            $mpginfo->bVariableBitrate = true;
        }
        $mpginfo->iLastBitrate = $mpeg_bitrate;

//Determine sampling rate
        switch ((ord($baseptr[$index + 2]) >> 2) & 0x03) {
            case 0x00:
                if ($mpeg_version == 1) {
                    $mpeg_sampling_rate = 44100;
                } elseif ($mpeg_version == 2) {
                    $mpeg_sampling_rate = 22050;
                } else {
                    $mpeg_sampling_rate = 11025;
                }
                break;
            case 0x01:
                if ($mpeg_version == 1) {
                    $mpeg_sampling_rate = 48000;
                } elseif ($mpeg_version == 2) {
                    $mpeg_sampling_rate = 24000;
                } else {
                    $mpeg_sampling_rate = 12000;
                }
                break;
            case 0x02:
                if ($mpeg_version == 1) {
                    $mpeg_sampling_rate = 32000;
                } elseif ($mpeg_version == 2) {
                    $mpeg_sampling_rate = 16000;
                } else {
                    $mpeg_sampling_rate = 8000;
                }
                break;
            default:
                $mpginfo->mpeg_stream_error = $index;
                return -1;
        }

//Check if padding is being used
        if ((ord($baseptr[$index + 2]) >> 1) & 0x01) {
            $mpeg_padding = 1;
        } else {
            $mpeg_padding = 0;
        }

//Check if frame is stereo
        if ((ord($baseptr[$index + 3]) & 0xC0) == 0xC0) {
            $mpginfo->LastFrameStereo = false;
        } else {
            $mpginfo->LastFrameStereo = true;
        }

        $mpginfo->iLastMPEGVersion = $mpeg_version;
        $mpginfo->iLastMPEGLayer = $mpeg_layer;

        if ($mpeg_layer == 1) {
            // floor is default in C++
            $iFrameSize = (floor(12 * $mpeg_bitrate * 1000 / $mpeg_sampling_rate) + $mpeg_padding) * 4;
        } elseif ($mpeg_layer == 2) {
            $iFrameSize = floor(144 * $mpeg_bitrate * 1000 / $mpeg_sampling_rate) + $mpeg_padding;
        } elseif ($mpeg_layer == 3 && $mpeg_version == 1) {
            $iFrameSize = floor(144 * $mpeg_bitrate * 1000 / $mpeg_sampling_rate) + $mpeg_padding;
        } else {
            $iFrameSize = floor(72 * $mpeg_bitrate * 1000 / $mpeg_sampling_rate) + $mpeg_padding;
        }

        $mpginfo->iTotalMPEGBytes+=$iFrameSize;

        return $iFrameSize;
    }

    private function CheckMP3CRC($baseptr, $index, $mpginfo) {
        $crc = 0xFFFF;
        $storedcrc = 0;
        $iSideInfoSize;
        $crc = $this->CalculateCRC16($crc, 0x8005, $baseptr[$index + 2], 2);

        if ($mpginfo->LastFrameStereo) {
            if ($mpginfo->iLastMPEGVersion == 1) {
                $iSideInfoSize = 32;
            } else {
                $iSideInfoSize = 17;
            }
        } else {
            if ($mpginfo->iLastMPEGVersion == 1) {
                $iSideInfoSize = 17;
            } else {
                $iSideInfoSize = 9;
            }
        }

        $crc = $this->CalculateCRC16($crc, 0x8005, $baseptr[$index + 6], $iSideInfoSize);

        $storedcrc[1] = $baseptr[$index + 4];
        $storedcrc[0] = $baseptr[$index + 5];

        if ($storedcrc != $crc) {
            $mpginfo->bCRCError = true;
            $mpginfo->iCRCErrors++;
        }
        return 0;
    }
    
    private function ValidateID3v2Tag($baseptr,$index, $mpginfo) {

	$mpginfo->id3v2++;

	$iDataSize=$baseptr[$index+9];
	$iDataSize+=128*$baseptr[$index+8];
	$iDataSize+=16384*$baseptr[$index+7];
	$iDataSize+=2097152*$baseptr[$index+6];

	if(ord($baseptr[$index+5])&0x10) return $iDataSize+20;
	return $iDataSize+10;

    }

    private function validate() {
        $baseptr = $this->bytes;
        $iFileSize = strlen($this->bytes);
        $mpginfo = $this->mpginfo;
        $iFrame = 0;
        $iFrameSize = 0;
        $iLastMPEGFrame = 0;
        $iNewFrame = 0;
        $WasFirstFrame = false;
        $iXingOffset = 0;
        $iID3v1Offset = 0;
        $LastFrameWasMPEG = false;

        $mpginfo->clear();

        if ($iFileSize >= 128 && !substr_compare($baseptr, "TAG", $iFileSize - 128, 3)) {
            $mpginfo->id3v1 = 1;
            $iFileSize-=128;
            $iID3v1Offset = $iFileSize;
        }

        if (($iFileSize >= 4) && !substr_compare($baseptr, "ID3", $iFrame, 3)) {
            if ($iFrame + 10 > $iFileSize) {
                $mpginfo->truncated = $iFrame;
            } else {
                $iFrame+=$this->ValidateID3v2Tag($baseptr, $iFrame, $mpginfo);
            }
        }

        while ($iFrame != $iFileSize) {
            if ($iFrame + 4 > $iFileSize) {
//Bad (unknown) frame
                $mpginfo->truncated = $iFrame;
                break;
            }
            if (!substr_compare($baseptr, 'RIFF', $iFrame, 4)) {
                if (!$WasFirstFrame) {
//This is actually a WAV file, not MPEG. Parsing RIFF header
                    $mpginfo->riff = $iFrame;
                    $iNewFrame = ParseRIFFHeader($baseptr, $iFrame, $iFileSize, $iFileSize);
                    if ($iNewFrame != -1) {
                        $iFrame = $iNewFrame;
                        continue;
                    }
                }
            }

            if ((ord($baseptr[$iFrame]) == 0xFF) && ((ord($baseptr[$iFrame + 1]) & 0xE0) == 0xE0)) {
//MPEG frame
                $iFrameSize = $this->ValidateMPEGFrame($baseptr, $iFrame, $mpginfo);
                if ($iFrameSize != -1) {
                    if ($iFrameSize + $iFrame <= $iFileSize && $mpginfo->iLastMPEGLayer == 3 && $mpginfo->bLastFrameCRC)
                        $this->CheckMP3CRC($baseptr, $iFrame, $mpginfo);
                    if (!$WasFirstFrame) {
                        $WasFirstFrame = true;
                        $mpginfo->iFirstMPEGFrameSize = $iFrameSize;
                        if ($mpginfo->iLastMPEGVersion == 1) {
                            if ($mpginfo->LastFrameStereo) {
                                $iXingOffset = 32;
                            } else {
                                $iXingOffset = 17;
                            }
                        } else {
                            if ($mpginfo->LastFrameStereo) {
                                $iXingOffset = 17;
                            } else {
                                $iXingOffset = 9;
                            }
                        }
                        //if (!$mpginfo->VBRHeaderPresent) ParseVBRIHeader($baseptr, $iFrame + 4 + 32, $mpginfo);
                    }
                    $iLastMPEGFrame = $iFrame;
                    $iFrame+=$iFrameSize;
                    continue;
                }
            }
//APEv2 tag
            if (!substr_compare($baseptr, "APET", $iFrame, 4)) {
                if ($iFrame + 16 > $iFileSize) {
                    $mpginfo->truncated = $iFrame;
                    break;
                }
                $iFrameSize = $this->ValidateAPEv2Tag($baseptr, $iFrame, $mpginfo);
                $iFrame+=$iFrameSize;
                continue;
            }

            if ($LastFrameWasMPEG) {
                $mpginfo->iDeletedFrames++;
                $mpginfo->iTotalMPEGBytes-=GetLastFrameSize();
            }

            if (!$iFrame) {
                $iNewFrame = $this->MPEGResync($baseptr, $iFrame, $iFileSize, 8);
                if ($iNewFrame == -1) {
                    $mpginfo->unknown_format = 0;
                    break;
                }
                $mpginfo->garbage_at_the_begin = 0;
                $this->warnings[] = "Garbage at the beginning of the file {$mpginfo->garbage_at_the_begin}";
                $mpginfo->iErrors++;
                $iFrame = $iNewFrame;
            } else {
                $iNewFrame = $this->MPEGResync($baseptr, $iLastMPEGFrame ? ($iLastMPEGFrame + 1) : $iFrame, $iFileSize, 6);
                if ($iNewFrame == -1) {
                    $mpginfo->garbage_at_the_end = $iFrame;
                    $this->warnings[] = "Garbage at the end of the file {$mpginfo->garbage_at_the_end}";
                    $mpginfo->iErrors++;
                    break;
                }
                $mpginfo->mpeg_stream_error = $iFrame;
                $this->warnings[] = "MPEG stream error, resynchronized successfully {$mpginfo->mpeg_stream_error}";
                $mpginfo->iErrors++;
                $iFrame = $iNewFrame;
            }
        }

        $mpeg_total =
                $mpginfo->mpeg1layer1 +
                $mpginfo->mpeg1layer2 +
                $mpginfo->mpeg1layer3 +
                $mpginfo->mpeg2layer1 +
                $mpginfo->mpeg2layer2 +
                $mpginfo->mpeg2layer3 +
                $mpginfo->mpeg25layer1 +
                $mpginfo->mpeg25layer2 +
                $mpginfo->mpeg25layer3;
        if ($mpeg_total < 10)
            $this->errors[] = "bad mp3 file\n";
        $this->mpeg_total = $mpeg_total;

        if ($mpginfo->truncated >= 0) {
            if ($LastFrameWasMPEG) {
                $mpginfo->iTotalMPEGBytes-=$iFrameSize;
                $mpginfo->iDeletedFrames++;
            }
        }

        return 0;
    }

}

$file = $_SERVER['argv'][1];
$fd = fopen($file, 'r');
$baseptr = fread($fd, 10000000);

$val = new mp3Validator($baseptr);
print_r ($val->getErrors());
print_r($val->getMPEGTotal())
?>
