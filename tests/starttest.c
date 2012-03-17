/*
 ============================================================================
 Author      : Mikhail Gordo
 Email       : mgordo@albany.edu
 Description : Selenium Test Starter
 Usage       : starttest [test id] [number of instances]
 ============================================================================
 */

#define FNAME 80
#define MAXTESTS 100

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/types.h>
#include <dirent.h>

int main(int pnum, char *par[]) {
	char input[FNAME];		// file names
	char temp[FNAME]; 		// temp variable
	int start=0, end=0;		// start and end
	char testId[FNAME];	// test id
	int inst = 0,i,k;	// test id and number of instances
	char runpath[FNAME];

	if (pnum != 3) {			// check the parameters
		inst = displayMenu(testId);
	} else {
		strcpy (testId, par[1]);
		strcpy (temp, par[2]);
		if (checkD(temp)==-1) {			// check if second parameter is a number
			inst = strtod(temp, NULL); 	// convert to double
		}
	}

	printf("\nSelected test: %s, Number of instances: %d\n", testId, inst);
	fflush(stdout);

	if (inst>9) {
		i = inst % 10;
		k = inst / 10;
		temp[0] = k + '0'; // convert number to string
		temp[1] = i + '0'; // convert number to string
		temp[2] = '\0';
	} else {
		temp[0] = inst + '0'; // convert number to string
		temp[1] = '\0';
	}

	strcpy(runpath,"./runner.sh ");
	strcat(runpath,testId);
	strcat(runpath," ");
	strcat(runpath,temp);
	system(runpath);

	printf("\nstarttest: ");
	analizeWorkers(inst);
	return 0;
}

// 	This function checks if string has only numbers
//	Input - string.
//	Output - -1 if success, otherwise number of non-number position
int checkD(char *c) {
	int r = -1, i;		//temp variables
	for (i = 0; i<strlen(c); i++) {
		if (!isdigit(c[i]) && c[i]!='-' )
			r = i;
	}
	return r;
}

int displayMenu(char *testId) {
	char str[MAXTESTS][FNAME];	// 100 files maximum
	int result;

	printf("\n  TESTS:\n");
	displayContent(*str);
	printf("\n  ENTER NUMBER OF TEST: ");
	scanf("%d",&result);
	result--;
	strcpy(testId, &str[result]);

	printf("  ENTER NUMBER OF INSTANCES: ");
	scanf("%d",&result);

	return result;
}

/*
 * This function displays only files with prefix "test"
 */
int displayContent(char str[MAXTESTS][FNAME]) {
    DIR *mydir;
    char cwd[1024];
    char *prefix = "test";
    char myprefix[FNAME];
    int i = 0;

    if (getcwd(cwd, sizeof(cwd)) != NULL) {
    	struct dirent *entry = NULL;
    	mydir =  opendir(cwd);
		while((entry = readdir(mydir))) /* If we get EOF, the expression is 0 and
										 * the loop stops. */
		{
			if (strcmp(entry->d_name, ".") != 0 && strcmp(entry->d_name, "..") != 0) {
				strncpy(myprefix,entry->d_name, strlen(prefix));
				if (strcmp(myprefix, prefix) == 0) {
					printf(" %2d. ",++i);
					printf("%s\n", entry->d_name);
					strcpy(&str[i-1],entry->d_name);
				}
			}
		}

    }
    closedir(mydir);
    return 0;
}


void analizeWorkers(int inst) {
	FILE *inp;
	int i,j,k;
	int flag = 0;
	char temp[FNAME];
	char fname[FNAME];
	char line[256]; /* or other suitable maximum line size */
	char *failure = "FAILURES!\n";

	for (i = 0; i < inst; i++) {
		if (i > 9) {
			j = i % 10;
			k = i / 10;
			temp[0] = k + '0'; 	// convert number to string
			temp[1] = j + '0';  // convert number to string
			temp[2] = '\0';
		} else {
			temp[0] = i + '0'; 	// convert number to string
			temp[1] = '\0';
		}

		strcpy(fname,"worker_");
		strcat(fname,temp);
		strcat(fname,".log");

		if ((inp = fopen (fname, "r")) == NULL) { // try to open the file
			fprintf(stderr, "Unable to open file %s\n", fname); fflush(stderr); exit(1);
		}

		while (fgets (line, sizeof line, inp ) != NULL ) {
			if(strcmp(failure, line)==0) {
				flag++;
				printf("\n file: %s   - ERROR DETECTED",fname);
			}
		}

		fclose (inp);
	}

	if (flag == 0) {
		printf("SUCCESS.\n\n");
	} else {
		printf("\nFAILURES! [%d]\n\n",flag);
	}
}
