# api试用micro 独立的体系, 占用更少的内存, 可以独立部署

尽量保持精小   
api层逻辑尽量简单, 在controller或者service层可以直接退出程序
不再局限于controller层才能输出信息, 在service遇到数据错误, 直接输出终止
