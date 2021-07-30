use v5.32;
use utf8;
use open qw(:std :utf8);
use Getopt::Long;
use Data::Dumper;
use autodie;
use Cwd;
use File::Find;
use File::Path qw(make_path);
use File::Spec::Functions 'catfile';
use File::Basename qw( fileparse );

my %mapping = (
    "SiteTree"        => 'SilverStripe\CMS\Model\SiteTree',
    "CWP.EventHolder" => 'CWP\CWP\PageTypes\EventHolder',
    "CWP.NewsHolder"  => 'CWP\CWP\PageTypes\NewsHolder',
    "Page"            => "Page",
);

my ( $inputFile, $dir, $outputDir );
my $root = getcwd;

GetOptions(
    'input=s'  => \$inputFile,
    'output=s' => \$outputDir,
    'dir=s'    => \$dir
);
&readClassMapping( $inputFile, \%mapping );

&main( \%mapping, $dir, $outputDir );

sub main {
    my ( $mapping, $dir, $outputDir ) = @_;
    my @content;
    finddepth(
        sub {
            &wanted( \@content, $_, $File::Find::name, \%mapping, $outputDir );
        },
        $dir
    );

    sub wanted {
        my ( $content, $filename, $fullPath, $mapping, $outputDir ) = @_;
        &convertClass( $filename, $mapping, $outputDir, $fullPath );
        push @$content, $fullPath if $filename =~ /\.(php|yml|ss)$/;
    }

    sub convertClass {
        my ( $inp, $mapping, $outputDir, $fullPath ) = @_;

        return if -d $inp;

        # input handle
        open my $fh, '<', $inp;

        my ( $__, $oriDir ) = fileparse $fullPath;
        my $dir     = catfile( $root, $outputDir, $oriDir );
        my @created = make_path($dir) if !-d $dir;
        say "Created dir $created[0]" if $created[0];

        # output handle
        my $outFile = catfile( $dir, $inp );
        open my $out, '>', $outFile;

        while (<$fh>) {

            foreach my $key ( sort { length $b <=> length $a } keys %$mapping )
            {
                my $value = $mapping->{$key};
                next if $_ !~ /$key/;
                if ( $inp =~ /\.php$/ ) {
                    $_ =~ s/'$key\.([^']+)'/$value:class . '$1'/g;
                }
                else {
                    $_ =~ s/$key/$value/g;
                }
                last if $_ =~ /$key/;
            }
            print $out $_;
        }
        close $fh;
        close $out;
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
