ssh nedlosster@85.94.32.195 -o ServerAliveInterval=60 -4 -p 32223  \
      -L 3128:127.0.0.1:3128  \
      -L 15432:eridanus.mcn.ru:5432 \
      -L 15433:reg99.mcntelecom.ru:5432 \
      -L 15434:vpbx.mcn.ru:5432 \
      -L 5038:127.0.0.1:5038
