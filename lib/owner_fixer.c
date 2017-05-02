#include <pwd.h>
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>
#include <errno.h>
#include <sys/types.h>
#include <sys/stat.h>

#define FILE_DEFAULT_PERMISSION 439   //octal 667
#define DIRECTORY_DEFAULT_PERMISSION 511    //octal 777

int main(int argc, char *argv[])
{
    //get owner of file
    int userUid=atoi(argv[2]);

    if(userUid<600){
        fprintf(stderr, "File owner not valid ldap user!\n");
        exit(EXIT_FAILURE);
    }

    struct stat cur_file;

    //check file is exist or not
    if (stat(argv[1], &cur_file) == -1) {
        perror("stat");
        exit(EXIT_FAILURE);
    }

    int res = chown(argv[1],userUid,300);
    
    //check result
    if(res==-1){
        perror("chown");
        exit(EXIT_FAILURE);
    }

    else{
        //if it is directory
        if(S_ISDIR(cur_file.st_mode)){
            chmod(argv[1],(DIRECTORY_DEFAULT_PERMISSION-strtol(argv[3],0,8)));
        }
        //if it is ffile
        else{
            chmod(argv[1],(FILE_DEFAULT_PERMISSION-strtol(argv[3],0,8)));
        }
    }
    exit(EXIT_SUCCESS);
}

