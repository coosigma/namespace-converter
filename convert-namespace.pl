use v5.32;
use utf8;
use open qw(:std :utf8);
use Getopt::Long;
use Data::Dumper;
use autodie;
use File::Find;

my %mapping = (
    "SiteTree"        => 'SilverStripe\CMS\Model\SiteTree',
    "CWP.EventHolder" => 'CWP\CWP\PageTypes\EventHolder',
    "CWP.NewsHolder"  => 'CWP\CWP\PageTypes\NewsHolder',
    "Page"            => "Page",
);

my ( $inputFile, $dir );

GetOptions(
    'input=s' => \$inputFile,
    'dir=s'   => \$dir
);

&readClassMapping( $inputFile, \%mapping );

&main( \%mapping, $dir );

sub main {
    my ( $mapping, $dir ) = @_;
    my @content;
    finddepth( \&wanted, $dir );
    print Dumper( \@content );

    sub wanted {
        say $_;
        push @content, $File::Find::name if $_ =~ /\.(php|yml|ss)$/;
    }

}

sub readClassMapping {
    my ( $inp, $res ) = @_;

    open my $fh, '<', $inp;

    while (<$fh>) {
        my @column = $_ =~ /([a-zA-Z_]+): (\S+)/;
        $res->{ $column[0] } = $column[1] if $column[1];
    }

    close $fh;
}
